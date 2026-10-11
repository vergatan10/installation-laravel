<?php

namespace Vergatan10\Installer\Http\Controllers;

use Vergatan10\Installer\Support\DatabaseManager;
use Vergatan10\Installer\Support\EnvironmentManager;
use Vergatan10\Installer\Support\Installer;
use Vergatan10\Installer\Support\ServerChecker;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;
use Throwable;

class InstallerController extends Controller
{
    public function __construct(
        protected ServerChecker $checker,
        protected DatabaseManager $database,
    ) {
    }

    public function welcome()
    {
        return view('installer::welcome', ['step' => 'welcome']);
    }

    public function requirements()
    {
        $items = $this->checker->requirements();
        $passes = ServerChecker::passes($items);
        $this->markDone('requirements', $passes);

        return view('installer::check', [
            'step' => 'requirements',
            'title' => 'Server Requirements',
            'intro' => 'Your server must meet the following requirements.',
            'items' => $items,
            'passes' => $passes,
            'next' => route('installer.permissions'),
            'back' => route('installer.welcome'),
        ]);
    }

    public function permissions()
    {
        if ($redirect = $this->guard('requirements')) {
            return $redirect;
        }

        $items = $this->checker->permissions();
        $passes = ServerChecker::passes($items);
        $this->markDone('permissions', $passes);

        return view('installer::check', [
            'step' => 'permissions',
            'title' => 'Folder Permissions',
            'intro' => 'These files and folders must be writable by the web server (usually 775 for folders, 664 for files).',
            'items' => $items,
            'passes' => $passes,
            'next' => route('installer.database'),
            'back' => route('installer.requirements'),
        ]);
    }

    public function database(Request $request)
    {
        if ($redirect = $this->guard('permissions')) {
            return $redirect;
        }

        $saved = session('installer.database', []);

        return view('installer::database', [
            'step' => 'database',
            'drivers' => config('installer.database_drivers'),
            'values' => array_merge([
                'app_name' => config('app.name', 'Laravel'),
                'app_url' => $request->root(),
                'app_env' => 'production',
                'app_debug' => false,
                'connection' => 'mysql',
                'host' => '127.0.0.1',
                'port' => '3306',
                'database' => '',
                'username' => '',
                'password' => '',
                'sqlite_path' => database_path('database.sqlite'),
            ], $saved),
        ]);
    }

    public function testDatabase(Request $request): JsonResponse
    {
        if ($this->guard('permissions')) {
            return response()->json(['ok' => false, 'message' => 'Please complete the previous steps first.'], 422);
        }

        $data = $this->normalizeDatabase($request->validate($this->databaseRules($request)));
        $error = $this->database->test($data);

        return response()->json([
            'ok' => $error === null,
            'message' => $error ?? 'Connection successful!',
        ]);
    }

    public function saveDatabase(Request $request): RedirectResponse
    {
        if ($redirect = $this->guard('permissions')) {
            return $redirect;
        }

        $input = $request->validate(array_merge($this->databaseRules($request), [
            'app_name' => ['required', 'string', 'max:100'],
            'app_url' => ['required', 'url'],
            'app_env' => ['required', Rule::in(['production', 'local', 'staging'])],
            'app_debug' => ['nullable', 'boolean'],
        ]));
        $input['app_debug'] = $request->boolean('app_debug');

        $data = $this->normalizeDatabase($input);

        if ($error = $this->database->test($data)) {
            return back()->withInput()->withErrors(['connection' => 'Could not connect to the database: '.$error]);
        }

        $written = $this->env()->set(array_merge([
            'APP_NAME' => $input['app_name'],
            'APP_URL' => rtrim($input['app_url'], '/'),
        ], $this->database->envValues($data)));

        if (! $written) {
            return back()->withInput()->withErrors(['connection' => 'The .env file is not writable.']);
        }

        session([
            'installer.database' => array_merge($request->except(['_token', 'password']), ['app_debug' => $input['app_debug']]),
            'installer.app' => ['APP_ENV' => $input['app_env'], 'APP_DEBUG' => $input['app_debug']],
        ]);
        $this->markDone('database');

        return redirect()->route(config('installer.admin.enabled') ? 'installer.admin' : 'installer.run');
    }

    public function admin()
    {
        if (! config('installer.admin.enabled')) {
            return redirect()->route('installer.run');
        }

        if ($redirect = $this->guard('database')) {
            return $redirect;
        }

        return view('installer::admin', [
            'step' => 'admin',
            'values' => session('installer.admin', []),
        ]);
    }

    public function saveAdmin(Request $request): RedirectResponse
    {
        if ($redirect = $this->guard('database')) {
            return $redirect;
        }

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        session(['installer.admin' => [
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ]]);
        $this->markDone('admin');

        return redirect()->route('installer.run');
    }

    public function run()
    {
        if ($redirect = $this->guard(config('installer.admin.enabled') ? 'admin' : 'database')) {
            return $redirect;
        }

        return view('installer::run', [
            'step' => 'run',
            'tasks' => $this->tasks(),
        ]);
    }

    public function runTask(string $task): JsonResponse
    {
        if ($this->guard(config('installer.admin.enabled') ? 'admin' : 'database')) {
            return response()->json(['status' => 'error', 'message' => 'Please complete the previous steps first.'], 422);
        }

        abort_unless(array_key_exists($task, $this->tasks()), 404);

        @set_time_limit(0);
        @ignore_user_abort(true);

        try {
            [$status, $message, $output] = $this->{'task'.ucfirst($task)}() + [2 => ''];
        } catch (Throwable $e) {
            report($e);
            [$status, $message, $output] = ['error', $e->getMessage(), ''];
        }

        $response = compact('status', 'message', 'output');

        if ($task === 'finish' && $status === 'ok') {
            $response['redirect'] = url(config('installer.redirect_after_install', '/'));
            $response['login'] = config('installer.admin.enabled') ? url(config('installer.login_url', '/login')) : null;
        }

        return response()->json($response);
    }

    /* ---------------------------------------------------------------------
     | Installation tasks
     * ------------------------------------------------------------------- */

    protected function tasks(): array
    {
        return array_filter([
            'environment' => 'Writing environment configuration',
            'key' => 'Generating application key',
            'migrate' => config('installer.migrate') ? 'Running database migrations' : null,
            'seed' => config('installer.seed') ? 'Seeding the database' : null,
            'storage' => config('installer.storage_link') ? 'Creating storage link' : null,
            'admin' => config('installer.admin.enabled') ? 'Creating administrator account' : null,
            'commands' => config('installer.commands') ? 'Running post-install commands' : null,
            'optimize' => 'Clearing caches',
            'finish' => 'Finishing installation',
        ]);
    }

    protected function taskEnvironment(): array
    {
        $values = session('installer.app', ['APP_ENV' => 'production', 'APP_DEBUG' => false]);

        if (! $this->env()->set($values)) {
            return ['error', 'Could not write the .env file.'];
        }

        return ['ok', 'APP_ENV='.$values['APP_ENV'].', APP_DEBUG='.($values['APP_DEBUG'] ? 'true' : 'false')];
    }

    protected function taskKey(): array
    {
        // The key is generated on the very first request of a fresh upload
        // (see InstallerServiceProvider). Regenerating it now would invalidate
        // the installer session, so only create one if it is still missing.
        if (empty($this->env()->get('APP_KEY'))) {
            Artisan::call('key:generate', ['--force' => true]);

            return ['ok', 'New application key generated.', Artisan::output()];
        }

        return ['ok', 'Application key is set.'];
    }

    protected function taskMigrate(): array
    {
        Artisan::call('migrate', ['--force' => true]);

        return ['ok', 'Migrations completed.', Artisan::output()];
    }

    protected function taskSeed(): array
    {
        $params = ['--force' => true];

        if ($seeder = config('installer.seeder')) {
            $params['--class'] = $seeder;
        }

        Artisan::call('db:seed', $params);

        return ['ok', 'Database seeded.', Artisan::output()];
    }

    protected function taskStorage(): array
    {
        if (file_exists(public_path('storage')) || is_link(public_path('storage'))) {
            return ['ok', 'public/storage already exists.'];
        }

        if (! function_exists('symlink')) {
            return ['warning', 'symlink() is disabled on this server. Create the public/storage link manually.'];
        }

        try {
            Artisan::call('storage:link');

            return ['ok', 'Storage link created.', Artisan::output()];
        } catch (Throwable $e) {
            return ['warning', 'Could not create storage link: '.$e->getMessage()];
        }
    }

    protected function taskAdmin(): array
    {
        $data = session('installer.admin');

        if (! $data) {
            return ['error', 'Administrator data is missing, please go back to the admin step.'];
        }

        $modelClass = config('installer.admin.model');
        $user = $modelClass::query()->firstOrNew(['email' => $data['email']]);
        $attributes = ['name' => $data['name'], 'email' => $data['email'], 'password' => $data['password']];

        if (Schema::hasColumn($user->getTable(), 'email_verified_at')) {
            $attributes['email_verified_at'] = now();
        }

        $user->forceFill(array_merge($attributes, config('installer.admin.attributes', [])))->save();

        if (($role = config('installer.admin.role')) && method_exists($user, 'assignRole')) {
            $user->assignRole($role);
        }

        return ['ok', 'Administrator '.$data['email'].' is ready.'];
    }

    protected function taskCommands(): array
    {
        $output = '';

        foreach (config('installer.commands', []) as $command => $params) {
            if (is_int($command)) {
                [$command, $params] = [$params, []];
            }

            Artisan::call($command, $params);
            $output .= '> '.$command.PHP_EOL.Artisan::output().PHP_EOL;
        }

        return ['ok', 'Post-install commands completed.', $output];
    }

    protected function taskOptimize(): array
    {
        try {
            Artisan::call('optimize:clear');

            return ['ok', 'Caches cleared.', Artisan::output()];
        } catch (Throwable $e) {
            return ['warning', 'Could not clear all caches: '.$e->getMessage()];
        }
    }

    protected function taskFinish(): array
    {
        if (! Installer::markInstalled()) {
            return ['error', 'Could not write the lock file '.Installer::lockPath()];
        }

        session()->forget('installer');

        return ['ok', 'Installation completed.'];
    }

    /* ---------------------------------------------------------------------
     | Helpers
     * ------------------------------------------------------------------- */

    protected function databaseRules(Request $request): array
    {
        $sqlite = $request->input('connection') === 'sqlite';

        return [
            'connection' => ['required', Rule::in(array_keys(config('installer.database_drivers')))],
            'host' => [$sqlite ? 'nullable' : 'required', 'string', 'max:255'],
            'port' => [$sqlite ? 'nullable' : 'required', 'numeric'],
            'database' => [$sqlite ? 'nullable' : 'required', 'string', 'max:255'],
            'username' => [$sqlite ? 'nullable' : 'required', 'string', 'max:255'],
            'password' => ['nullable', 'string', 'max:255'],
            'sqlite_path' => [$sqlite ? 'required' : 'nullable', 'string', 'max:500'],
        ];
    }

    protected function normalizeDatabase(array $input): array
    {
        if ($input['connection'] === 'sqlite') {
            return ['connection' => 'sqlite', 'database' => $input['sqlite_path']];
        }

        return [
            'connection' => $input['connection'],
            'host' => $input['host'],
            'port' => (string) $input['port'],
            'database' => $input['database'],
            'username' => $input['username'],
            'password' => $input['password'] ?? '',
        ];
    }

    protected function env(): EnvironmentManager
    {
        return new EnvironmentManager(app()->environmentFilePath());
    }

    protected function markDone(string $step, bool $passes = true): void
    {
        $done = session('installer.done', []);
        $done[$step] = $passes;
        session(['installer.done' => $done]);
    }

    /**
     * Redirect back to the first unfinished step before $required.
     */
    protected function guard(string $required): ?RedirectResponse
    {
        $order = ['requirements', 'permissions', 'database', 'admin'];
        $done = session('installer.done', []);

        foreach (array_slice($order, 0, array_search($required, $order) + 1) as $step) {
            if ($step === 'admin' && ! config('installer.admin.enabled')) {
                continue;
            }

            if (empty($done[$step])) {
                return redirect()->route('installer.'.$step);
            }
        }

        return null;
    }
}
