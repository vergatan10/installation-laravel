<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Route prefix
    |--------------------------------------------------------------------------
    | The installer is served at https://your-domain.com/{prefix}
    */
    'route_prefix' => 'install',

    /*
    |--------------------------------------------------------------------------
    | Language
    |--------------------------------------------------------------------------
    | Default installer language and the languages offered in the switcher
    | (the switcher is hidden when only one language is listed).
    | Bundled translations: en, id. Publish with --tag=installer-lang to edit.
    */
    'locale' => 'en',
    'locales' => [
        'en' => 'English',
        'id' => 'Bahasa Indonesia',
    ],

    /*
    |--------------------------------------------------------------------------
    | Installer key (optional)
    |--------------------------------------------------------------------------
    | When set, the installer asks for this key before anything else, so a
    | stranger cannot install a freshly uploaded copy. Leave empty to disable.
    | Usually set via INSTALLER_KEY in .env.example (read even before .env exists).
    */
    'access_key' => env('INSTALLER_KEY'),

    /*
    |--------------------------------------------------------------------------
    | Lock file
    |--------------------------------------------------------------------------
    | Written to storage/ when installation finishes. While it exists the
    | installer is disabled and every /install URL redirects to the app.
    */
    'lock_file' => 'installed',

    /*
    |--------------------------------------------------------------------------
    | Redirects
    |--------------------------------------------------------------------------
    */
    'redirect_after_install' => '/',
    'login_url' => '/login',

    /*
    |--------------------------------------------------------------------------
    | Paths that are NOT redirected to the installer before installation
    |--------------------------------------------------------------------------
    | Request path patterns (Request::is() syntax), e.g. 'up', 'api/*'.
    */
    'except' => [
        'up',
    ],

    /*
    |--------------------------------------------------------------------------
    | Server requirements
    |--------------------------------------------------------------------------
    */
    'requirements' => [
        'php' => '8.1.0',
        'extensions' => [
            'bcmath',
            'ctype',
            'curl',
            'fileinfo',
            'json',
            'mbstring',
            'openssl',
            'pdo',
            'tokenizer',
            'xml',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Writable paths (relative to the project root)
    |--------------------------------------------------------------------------
    */
    'permissions' => [
        'storage/app',
        'storage/framework',
        'storage/framework/cache',
        'storage/framework/sessions',
        'storage/framework/views',
        'storage/logs',
        'bootstrap/cache',
        '.env',
    ],

    /*
    |--------------------------------------------------------------------------
    | Database drivers offered on the database page
    |--------------------------------------------------------------------------
    */
    'database_drivers' => [
        'mysql' => 'MySQL',
        'mariadb' => 'MariaDB',
        'pgsql' => 'PostgreSQL',
        'sqlite' => 'SQLite',
        'sqlsrv' => 'SQL Server',
    ],

    /*
    |--------------------------------------------------------------------------
    | Installation tasks
    |--------------------------------------------------------------------------
    */
    'migrate' => true,

    // Run db:seed. Make sure your seeder does NOT depend on dev packages
    // (e.g. Faker/factories) when you deploy with `composer install --no-dev`.
    'seed' => true,
    'seeder' => null, // e.g. 'Database\\Seeders\\ProductionSeeder' (null = DatabaseSeeder)

    'storage_link' => true,

    // Extra artisan commands executed after migrate/seed, e.g.
    // ['permission:cache-reset', 'app:sync-settings' => ['--force' => true]]
    'commands' => [],

    /*
    |--------------------------------------------------------------------------
    | Extra .env settings (optional page)
    |--------------------------------------------------------------------------
    | APP_* and DB_* are always asked on the Database page. Add groups here for
    | anything else your app needs in .env (mail, payment gateway, API keys...).
    | When 'groups' is empty the Settings page is skipped entirely.
    |
    | Each field key is the .env variable name. Field options:
    |   label, type (text|email|password|number|url|tel|select|boolean),
    |   rules, options (for select), default, hint
    | Prefilled from the current .env (e.g. values from .env.example).
    | Labels/titles may be plain text or translation keys.
    */
    'settings' => [
        'title' => null,        // null = translated "Settings"
        'description' => null,
        'step_label' => null,

        'groups' => [
            // 'mail' => [
            //     'title' => 'Email (SMTP)',
            //     'description' => 'Used to send password resets and notifications.',
            //     'fields' => [
            //         'MAIL_MAILER' => ['label' => 'Mailer', 'type' => 'select', 'options' => ['smtp' => 'SMTP', 'log' => 'Log (do not send)'], 'default' => 'smtp', 'rules' => 'required'],
            //         'MAIL_HOST' => ['label' => 'SMTP Host', 'rules' => 'nullable|string'],
            //         'MAIL_PORT' => ['label' => 'SMTP Port', 'type' => 'number', 'default' => 587, 'rules' => 'nullable|integer'],
            //         'MAIL_USERNAME' => ['label' => 'SMTP Username', 'rules' => 'nullable|string'],
            //         'MAIL_PASSWORD' => ['label' => 'SMTP Password', 'type' => 'password', 'rules' => 'nullable|string'],
            //         'MAIL_FROM_ADDRESS' => ['label' => 'From Address', 'type' => 'email', 'rules' => 'nullable|email'],
            //     ],
            // ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Administrator account
    |--------------------------------------------------------------------------
    | When enabled, the wizard shows an account page and creates (or updates)
    | that user after migrations & seeders ran.
    | See README "Akun Super Admin" for examples.
    */
    'admin' => [
        'enabled' => true,

        // Texts shown on the account page and in the stepper
        // (null = translated default). Plain text or translation keys.
        'title' => null,
        'description' => null,
        'step_label' => null,

        'model' => 'App\Models\User',

        // Column used to find an existing user (re-running the installer
        // updates that user instead of creating a duplicate).
        'identifier' => 'email',

        // Form fields. Key = input name (and model column unless 'column' says otherwise).
        //   label    : field label (plain text or translation key)
        //   type     : text | email | password | tel | number | url | select
        //   rules    : Laravel validation rules ('confirmed' adds a "Confirm ..." input)
        //   options  : for type=select, ['value' => 'Label']
        //   hint     : small help text under the field
        //   column   : model column to save to, or false to only pass it to the callback
        //   default  : prefilled value
        // Password fields are hashed automatically.
        'fields' => [
            'name' => [
                'label' => 'installer::installer.admin.name',
                'type' => 'text',
                'rules' => 'required|string|max:255',
            ],
            'email' => [
                'label' => 'installer::installer.admin.email',
                'type' => 'email',
                'rules' => 'required|email|max:255',
            ],
            'password' => [
                'label' => 'installer::installer.admin.password',
                'type' => 'password',
                'rules' => 'required|string|min:8|confirmed',
            ],
        ],

        // Extra columns forced onto the admin user, e.g. ['is_admin' => true]
        'attributes' => [],

        // Role name assigned via ->assignRole() (spatie/laravel-permission), or null
        'role' => null,

        // Invokable class called after the user is saved, for anything custom:
        //   public function __invoke($user, array $data): void
        // $data holds all submitted field values (passwords already hashed).
        'callback' => null,
    ],

];
