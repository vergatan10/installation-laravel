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
    | When enabled, the wizard asks for an admin name/email/password and
    | creates (or updates) that user after migrations & seeders ran.
    */
    'admin' => [
        'enabled' => true,
        'model' => 'App\\Models\\User',
        // Extra columns forced onto the admin user, e.g. ['is_admin' => true]
        'attributes' => [],
        // Role name assigned via ->assignRole() (spatie/laravel-permission), or null
        'role' => null,
    ],

];
