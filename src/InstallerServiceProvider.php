<?php

namespace Vergatan10\Installer;

use Vergatan10\Installer\Console\LockCommand;
use Vergatan10\Installer\Console\UnlockCommand;
use Vergatan10\Installer\Http\Middleware\RedirectIfNotInstalled;
use Vergatan10\Installer\Support\EnvironmentManager;
use Vergatan10\Installer\Support\Installer;
use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider;

class InstallerServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/installer.php', 'installer');

        if (! $this->app->runningInConsole() && ! Installer::isInstalled()) {
            $this->prepareForInstallation();
        }
    }

    public function boot(Router $router): void
    {
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'installer');
        $this->loadRoutesFrom(__DIR__.'/../routes/web.php');

        if (! Installer::isInstalled()) {
            $router->pushMiddlewareToGroup('web', RedirectIfNotInstalled::class);
        }

        if ($this->app->runningInConsole()) {
            $this->commands([LockCommand::class, UnlockCommand::class]);

            $this->publishes([
                __DIR__.'/../config/installer.php' => config_path('installer.php'),
            ], 'installer-config');

            $this->publishes([
                __DIR__.'/../resources/views' => resource_path('views/vendor/installer'),
            ], 'installer-views');
        }
    }

    /**
     * Make a fresh, unconfigured upload bootable: create .env, give it an
     * APP_KEY and keep sessions/cache off the (not yet configured) database.
     */
    protected function prepareForInstallation(): void
    {
        $env = new EnvironmentManager($this->app->environmentFilePath());
        $env->ensureExists(base_path('.env.example'));

        if (empty(config('app.key'))) {
            $key = $env->get('APP_KEY');

            if (empty($key)) {
                $key = $this->generateKey();

                if (! $env->set(['APP_KEY' => $key])) {
                    // .env is not writable: use a stable temporary key so the
                    // installer can still render and report the permission problem.
                    $length = str_contains(strtolower((string) config('app.cipher')), '128') ? 16 : 32;
                    $key = 'base64:'.base64_encode(substr(hash('sha256', base_path(), true), 0, $length));
                }
            }

            config(['app.key' => $key]);
        }

        // The default session cookie name is derived from APP_NAME, which the
        // wizard changes mid-way; pin it so the installer session survives.
        config([
            'session.cookie' => 'laravel_installer_session',
            'session.driver' => 'file',
            'cache.default' => 'file',
            'queue.default' => 'sync',
        ]);
    }

    protected function generateKey(): string
    {
        $length = str_contains(strtolower((string) config('app.cipher')), '128') ? 16 : 32;

        return 'base64:'.base64_encode(random_bytes($length));
    }
}
