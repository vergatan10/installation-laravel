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
    | Administrator account
    |--------------------------------------------------------------------------
    | When enabled, the wizard shows an account page right after the database
    | step and creates (or updates) that user after migrations & seeders ran.
    | See README "Akun Super Admin" for examples.
    */
    'admin' => [
        'enabled' => true,

        // Texts shown on the account page and in the stepper.
        'title' => 'Administrator Account',
        'description' => 'This account will be created after the database is installed. Use it to log in.',
        'step_label' => 'Admin',

        'model' => 'App\\Models\\User',

        // Column used to find an existing user (re-running the installer
        // updates that user instead of creating a duplicate).
        'identifier' => 'email',

        // Form fields. Key = input name (and model column unless 'column' says otherwise).
        //   label    : field label
        //   type     : text | email | password | tel | number | select
        //   rules    : Laravel validation rules ('confirmed' adds a "Confirm ..." input)
        //   options  : for type=select, ['value' => 'Label']
        //   hint     : small help text under the field
        //   column   : model column to save to, or false to only pass it to the callback
        //   default  : prefilled value
        // Password fields are hashed automatically.
        'fields' => [
            'name' => [
                'label' => 'Full Name',
                'type' => 'text',
                'rules' => 'required|string|max:255',
            ],
            'email' => [
                'label' => 'Email',
                'type' => 'email',
                'rules' => 'required|email|max:255',
            ],
            'password' => [
                'label' => 'Password',
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
