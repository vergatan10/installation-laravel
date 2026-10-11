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
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\RateLimiter;
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
        return view('installer::welcome', [
            'step' => 'welcome',
            'needsKey' => Installer::accessKey() && empty(session('installer.done.access')),
            'next' => $this->nextUrl('access'),
        ]);
    }

    public function access(Request $request): RedirectResponse
    {
        if (! Installer::accessKey()) {
            return redirect()->route('installer.requirements');
        }

        $throttle = 'installer-access:'.$request->ip();

        if (RateLimiter::tooManyAttempts($throttle, 5)) {
            return back()->withErrors(['access_key' => __('installer::installer.access.throttled', [
                'seconds' => RateLimiter::availableIn($throttle),
            ])]);
        }

        $request->validate(['access_key' => ['required', 'string']], $this->validationMessages(), [
            'access_key' => strtolower(__('installer::installer.access.label')),
        ]);

        if (! hash_equals(Installer::accessKey(), (string) $request->input('access_key'))) {
            RateLimiter::hit($throttle, 60);

            return back()->withErrors(['access_key' => __('installer::installer.access.invalid')]);
        }

        RateLimiter::clear($throttle);
        $request->session()->regenerate();
        $this->markDone('access');

        return redirect()->route('installer.requirements');
    }

    public function language(Request $request, string $locale): RedirectResponse
    {
        if (array_key_exists($locale, config('installer.locales', []))) {
            $request->session()->put('installer.locale', $locale);
        }

        return back(302, [], route('installer.welcome'));
    }

    public function requirements()
    {
        if ($redirect = $this->guard('requirements')) {
            return $redirect;
        }

        $items = $this->checker->requirements();
        $passes = ServerChecker::passes($items);
        $this->markDone('requirements', $passes);

        return view('installer::check', [
            'step' => 'requirements',
            'title' => __('installer::installer.requirements.title'),
            'intro' => __('installer::installer.requirements.intro'),
            'items' => $items,
            'passes' => $passes,
            'next' => $this->nextUrl('requirements'),
            'back' => route('installer.welcome'),
        ]);
    }

    public function permissions()
    {
        if ($redirect = $this->guard('permissions')) {
            return $redirect;
        }

        $items = $this->checker->permissions();
        $passes = ServerChecker::passes($items);
        $this->markDone('permissions', $passes);

        return view('installer::check', [
            'step' => 'permissions',
            'title' => __('installer::installer.permissions.title'),
            'intro' => __('installer::installer.permissions.intro'),
            'items' => $items,
            'passes' => $passes,
            'next' => $this->nextUrl('permissions'),
            'back' => $this->previousUrl('permissions'),
        ]);
    }

    public function database(Request $request)
    {
        if ($redirect = $this->guard('database')) {
            return $redirect;
        }

        $saved = session('installer.database', []);

        return view('installer::database', [
            'step' => 'database',
            'drivers' => config('installer.database_drivers'),
            'back' => $this->previousUrl('database'),
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
        if ($this->guard('database')) {
            return response()->json(['ok' => false, 'message' => __('installer::installer.common.previous_steps')], 422);
        }

        $data = $this->normalizeDatabase($this->validateDatabase($request));
        $error = $this->database->test($data);

        return response()->json([
            'ok' => $error === null,
            'message' => $error ?? __('installer::installer.database.connected'),
        ]);
    }

    public function saveDatabase(Request $request): RedirectResponse
    {
        if ($redirect = $this->guard('database')) {
            return $redirect;
        }

        $input = $this->validateDatabase($request, [
            'app_name' => ['required', 'string', 'max:100'],
            'app_url' => ['required', 'url'],
            'app_env' => ['required', Rule::in(['production', 'local', 'staging'])],
            'app_debug' => ['nullable', 'boolean'],
        ]);
        $input['app_debug'] = $request->boolean('app_debug');

        $data = $this->normalizeDatabase($input);

        if ($error = $this->database->test($data)) {
            return back()->withInput()->withErrors([
                'connection' => __('installer::installer.database.connect_failed', ['error' => $error]),
            ]);
        }

        $written = $this->env()->set(array_merge([
            'APP_NAME' => $input['app_name'],
            'APP_URL' => rtrim($input['app_url'], '/'),
        ], $this->database->envValues($data)));

        if (! $written) {
            return back()->withInput()->withErrors(['connection' => __('installer::installer.common.env_not_writable')]);
        }

        session([
            'installer.database' => array_merge($request->except(['_token', 'password']), ['app_debug' => $input['app_debug']]),
            'installer.app' => ['APP_ENV' => $input['app_env'], 'APP_DEBUG' => $input['app_debug']],
        ]);
        $this->markDone('database');

        return redirect($this->nextUrl('database'));
    }

    public function settings()
    {
        if (! Installer::settingsGroups()) {
            return redirect($this->nextUrl('settings'));
        }

        if ($redirect = $this->guard('settings')) {
            return $redirect;
        }

        $env = $this->env();
        $saved = session('installer.settings', []);
        $groups = [];
        $values = [];

        foreach (Installer::settingsGroups() as $name => $group) {
            $fields = Installer::fields($group['fields'] ?? []);

            foreach ($fields as $key => $field) {
                $current = $env->get($key);
                $current = in_array(strtolower((string) $current), ['null', '(null)'], true) ? '' : $current;

                $values[$key] = $saved[$key] ?? $current ?? $field['default'] ?? '';
            }

            $groups[$name] = [
                'title' => __($group['title'] ?? ucfirst($name)),
                'description' => isset($group['description']) ? __($group['description']) : null,
                'fields' => $fields,
            ];
        }

        return view('installer::settings', [
            'step' => 'settings',
            'groups' => $groups,
            'values' => $values,
            'back' => $this->previousUrl('settings'),
        ]);
    }

    public function saveSettings(Request $request): RedirectResponse
    {
        if ($redirect = $this->guard('settings')) {
            return $redirect;
        }

        $fields = [];

        foreach (Installer::settingsGroups() as $group) {
            $fields += Installer::fields($group['fields'] ?? []);
        }

        $data = $this->validateFields($request, $fields);
        $values = [];

        foreach ($fields as $key => $field) {
            $values[$key] = $field['type'] === 'boolean' ? $request->boolean($key) : (string) ($data[$key] ?? '');
        }

        if (! $this->env()->set($values)) {
            return back()->withInput()->withErrors(['settings' => __('installer::installer.common.env_not_writable')]);
        }

        session(['installer.settings' => $values]);
        $this->markDone('settings');

        return redirect($this->nextUrl('settings'));
    }

    public function admin()
    {
        if (! config('installer.admin.enabled')) {
            return redirect($this->nextUrl('admin'));
        }

        if ($redirect = $this->guard('admin')) {
            return $redirect;
        }

        $fields = Installer::fields(config('installer.admin.fields', []));
        $saved = session('installer.admin', []);
        $values = [];

        foreach ($fields as $key => $field) {
            $values[$key] = $field['type'] === 'password' ? '' : ($saved[$key] ?? $field['default'] ?? '');
        }

        return view('installer::admin', [
            'step' => 'admin',
            'fields' => $fields,
            'values' => $values,
            'back' => $this->previousUrl('admin'),
        ]);
    }

    public function saveAdmin(Request $request): RedirectResponse
    {
        if ($redirect = $this->guard('admin')) {
            return $redirect;
        }

        $fields = Installer::fields(config('installer.admin.fields', []));
        $data = $this->validateFields($request, $fields);

        foreach ($fields as $key => $field) {
            if ($field['type'] === 'password' && isset($data[$key]) && $data[$key] !== '') {
                $data[$key] = Hash::make($data[$key]);
            }
        }

        session(['installer.admin' => array_intersect_key($data, $fields)]);
        $this->markDone('admin');

        return redirect()->route('installer.run');
    }

    public function run()
    {
        if ($redirect = $this->guard('run')) {
            return $redirect;
        }

        return view('installer::run', [
            'step' => 'run',
            'tasks' => $this->tasks(),
            'back' => $this->previousUrl('run'),
        ]);
    }

    public function runTask(string $task): JsonResponse
    {
        if ($this->guard('run')) {
            return response()->json(['status' => 'error', 'message' => __('installer::installer.common.previous_steps')], 422);
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
        $t = fn (string $key, array $replace = []) => __('installer::installer.tasks.'.$key, $replace);

        return array_filter([
            'environment' => $t('environment'),
            'key' => $t('key'),
            'migrate' => config('installer.migrate') ? $t('migrate') : null,
            'seed' => config('installer.seed') ? $t('seed') : null,
            'storage' => config('installer.storage_link') ? $t('storage') : null,
            'admin' => config('installer.admin.enabled') ? $t('admin', ['title' => mb_strtolower($this->adminTitle())]) : null,
            'commands' => config('installer.commands') ? $t('commands') : null,
            'optimize' => $t('optimize'),
            'finish' => $t('finish'),
        ]);
    }

    protected function taskEnvironment(): array
    {
        $values = session('installer.app', ['APP_ENV' => 'production', 'APP_DEBUG' => false]);

        if (! $this->env()->set($values)) {
            return ['error', __('installer::installer.common.env_not_writable')];
        }

        return ['ok', $this->message('env_set', [
            'env' => $values['APP_ENV'],
            'debug' => $values['APP_DEBUG'] ? 'true' : 'false',
        ])];
    }

    protected function taskKey(): array
    {
        // The key is generated on the very first request of a fresh upload
        // (see InstallerServiceProvider). Regenerating it now would invalidate
        // the installer session, so only create one if it is still missing.
        if (empty($this->env()->get('APP_KEY'))) {
            Artisan::call('key:generate', ['--force' => true]);

            return ['ok', $this->message('key_generated'), Artisan::output()];
        }

        return ['ok', $this->message('key_set')];
    }

    protected function taskMigrate(): array
    {
        Artisan::call('migrate', ['--force' => true]);

        return ['ok', $this->message('migrated'), Artisan::output()];
    }

    protected function taskSeed(): array
    {
        $params = ['--force' => true];

        if ($seeder = config('installer.seeder')) {
            $params['--class'] = $seeder;
        }

        Artisan::call('db:seed', $params);

        return ['ok', $this->message('seeded'), Artisan::output()];
    }

    protected function taskStorage(): array
    {
        if (file_exists(public_path('storage')) || is_link(public_path('storage'))) {
            return ['ok', $this->message('storage_exists')];
        }

        if (! function_exists('symlink')) {
            return ['warning', $this->message('symlink_disabled')];
        }

        try {
            Artisan::call('storage:link');

            return ['ok', $this->message('storage_linked'), Artisan::output()];
        } catch (Throwable $e) {
            return ['warning', $this->message('storage_failed', ['error' => $e->getMessage()])];
        }
    }

    protected function taskAdmin(): array
    {
        $data = session('installer.admin');

        if (! $data) {
            return ['error', __('installer::installer.admin.missing')];
        }

        $attributes = [];

        foreach (Installer::fields(config('installer.admin.fields', [])) as $key => $field) {
            $column = $field['column'] ?? $key;

            if ($column !== false && array_key_exists($key, $data)) {
                $attributes[$column] = $data[$key];
            }
        }

        $attributes = array_merge($attributes, config('installer.admin.attributes', []));
        $identifier = config('installer.admin.identifier', 'email');

        if (! isset($attributes[$identifier])) {
            return ['error', __('installer::installer.admin.identifier_missing', ['column' => $identifier])];
        }

        $modelClass = config('installer.admin.model');
        $user = $modelClass::query()->firstOrNew([$identifier => $attributes[$identifier]]);

        if (Schema::hasColumn($user->getTable(), 'email_verified_at')) {
            $attributes['email_verified_at'] ??= now();
        }

        $user->forceFill($attributes)->save();

        if (($role = config('installer.admin.role')) && method_exists($user, 'assignRole')) {
            $user->assignRole($role);
        }

        if ($callback = config('installer.admin.callback')) {
            app($callback)($user, $data);
        }

        return ['ok', __('installer::installer.admin.ready', [
            'title' => $this->adminTitle(),
            'id' => $attributes[$identifier],
        ])];
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

        return ['ok', $this->message('commands_done'), $output];
    }

    protected function taskOptimize(): array
    {
        try {
            Artisan::call('optimize:clear');

            return ['ok', $this->message('caches_cleared'), Artisan::output()];
        } catch (Throwable $e) {
            return ['warning', $this->message('caches_failed', ['error' => $e->getMessage()])];
        }
    }

    protected function taskFinish(): array
    {
        if (! Installer::markInstalled()) {
            return ['error', $this->message('lock_failed', ['path' => Installer::lockPath()])];
        }

        session()->forget('installer');

        return ['ok', $this->message('completed')];
    }

    /* ---------------------------------------------------------------------
     | Helpers
     * ------------------------------------------------------------------- */

    protected function validateDatabase(Request $request, array $extra = []): array
    {
        $sqlite = $request->input('connection') === 'sqlite';
        $t = fn (string $key) => mb_strtolower(__('installer::installer.database.'.$key));

        return $request->validate(array_merge([
            'connection' => ['required', Rule::in(array_keys(config('installer.database_drivers')))],
            'host' => [$sqlite ? 'nullable' : 'required', 'string', 'max:255'],
            'port' => [$sqlite ? 'nullable' : 'required', 'numeric'],
            'database' => [$sqlite ? 'nullable' : 'required', 'string', 'max:255'],
            'username' => [$sqlite ? 'nullable' : 'required', 'string', 'max:255'],
            'password' => ['nullable', 'string', 'max:255'],
            'sqlite_path' => [$sqlite ? 'required' : 'nullable', 'string', 'max:500'],
        ], $extra), $this->validationMessages(), [
            'connection' => $t('type'),
            'host' => $t('host'),
            'port' => $t('port'),
            'database' => $t('name'),
            'username' => $t('username'),
            'password' => $t('password'),
            'sqlite_path' => $t('sqlite_path'),
            'app_name' => $t('app_name'),
            'app_url' => $t('app_url'),
            'app_env' => $t('environment'),
        ]);
    }

    protected function validateFields(Request $request, array $fields): array
    {
        return $request->validate(
            array_map(fn ($field) => $field['type'] === 'boolean' ? ['nullable', 'boolean'] : $field['rules'], $fields),
            $this->validationMessages(),
            array_map(fn ($field) => mb_strtolower($field['label']), $fields),
        );
    }

    /**
     * Bundled validation messages for the current locale, used only when the
     * application has no translations of its own for that locale.
     */
    protected function validationMessages(): array
    {
        $locale = app()->getLocale();

        if (Lang::hasForLocale('validation.required', $locale)) {
            return [];
        }

        $messages = __('installer::validation', [], $locale);

        return is_array($messages) ? $messages : [];
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

    protected function adminTitle(): string
    {
        return __(config('installer.admin.title') ?: 'installer::installer.admin.title');
    }

    protected function message(string $key, array $replace = []): string
    {
        return __('installer::installer.messages.'.$key, $replace);
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
     * Redirect to the first unfinished step that comes before $step.
     */
    protected function guard(string $step): ?RedirectResponse
    {
        $done = session('installer.done', []);

        foreach (Installer::steps() as $required) {
            if ($required === $step) {
                break;
            }

            if (empty($done[$required])) {
                return redirect($this->stepUrl($required));
            }
        }

        return null;
    }

    protected function stepUrl(string $step): string
    {
        return route($step === 'access' ? 'installer.welcome' : 'installer.'.$step);
    }

    protected function nextUrl(string $step): string
    {
        $steps = Installer::steps();
        $index = array_search($step, $steps, true);
        $next = $index === false ? null : ($steps[$index + 1] ?? null);

        // A step that is switched off: find where it would sit in the full order.
        if ($index === false) {
            $order = ['access', 'requirements', 'permissions', 'database', 'settings', 'admin'];

            foreach (array_slice($order, array_search($step, $order) + 1) as $candidate) {
                if (in_array($candidate, $steps, true)) {
                    $next = $candidate;
                    break;
                }
            }
        }

        return $next ? $this->stepUrl($next) : route('installer.run');
    }

    protected function previousUrl(string $step): string
    {
        $steps = Installer::steps();
        $index = $step === 'run' ? count($steps) : array_search($step, $steps, true);
        $previous = $index ? ($steps[$index - 1] ?? null) : null;

        return $previous ? $this->stepUrl($previous) : route('installer.welcome');
    }
}
