<?php

return [

    'wizard' => 'Installation Wizard',
    'language' => 'Language',

    'steps' => [
        'welcome' => 'Welcome',
        'requirements' => 'Requirements',
        'permissions' => 'Permissions',
        'database' => 'Database',
        'settings' => 'Settings',
        'admin' => 'Admin',
        'install' => 'Install',
    ],

    'common' => [
        'back' => 'Back',
        'next' => 'Next',
        'check_again' => 'Check Again',
        'ok' => 'OK',
        'failed' => 'FAILED',
        'enabled' => 'Enabled',
        'missing' => 'Missing',
        'not_found' => 'not found',
        'previous_steps' => 'Please complete the previous steps first.',
        'env_not_writable' => 'The .env file is not writable.',
        'form_errors' => 'Please check the form for errors.',
    ],

    'welcome' => [
        'title' => 'Welcome',
        'intro' => 'This wizard will install :app on your server in a few steps.',
        'prepare' => 'Before you start, please prepare:',
        'prepare_db' => 'An empty database (MySQL / MariaDB / PostgreSQL / SQL Server) or use SQLite',
        'prepare_db_credentials' => 'The database host, port, name, username and password',
        'prepare_settings' => 'Additional settings: :groups',
        'prepare_admin' => 'The details for the :title (:fields)',
        'info' => 'The installer will write your .env file, run the database migrations and seeders, create the storage link and lock itself when finished.',
        'start' => 'Check Requirements',
    ],

    'access' => [
        'title' => 'Installer Key',
        'intro' => 'This installer is protected. Enter the installer key you received to continue.',
        'label' => 'Installer Key',
        'submit' => 'Unlock',
        'invalid' => 'The installer key is incorrect.',
        'throttled' => 'Too many attempts. Please try again in :seconds seconds.',
        'unlocked' => 'Installer unlocked.',
    ],

    'requirements' => [
        'title' => 'Server Requirements',
        'intro' => 'Your server must meet the following requirements.',
        'php' => 'PHP >= :version',
        'extension' => ':name extension',
    ],

    'permissions' => [
        'title' => 'Folder Permissions',
        'intro' => 'These files and folders must be writable by the web server (usually 775 for folders, 664 for files).',
    ],

    'check' => [
        'failed' => 'Some checks failed. Fix them on your server (or ask your hosting provider), then click Check Again.',
    ],

    'database' => [
        'title' => 'Application & Database',
        'intro' => 'Create an empty database in your hosting panel (cPanel, Plesk, etc.), then enter its details below and click Test Connection.',
        'application' => 'Application',
        'app_name' => 'App Name',
        'app_url' => 'App URL',
        'environment' => 'Environment',
        'env_production' => 'Production',
        'env_staging' => 'Staging',
        'env_local' => 'Local / Development',
        'debug' => 'Enable debug mode',
        'debug_hint' => 'Keep this off on a live website.',
        'database' => 'Database',
        'type' => 'Database Type',
        'sqlite_path' => 'SQLite File Path',
        'sqlite_hint' => 'The file will be created if it does not exist.',
        'host' => 'Host',
        'port' => 'Port',
        'name' => 'Database Name',
        'username' => 'Username',
        'password' => 'Password',
        'test' => 'Test Connection',
        'testing' => 'Testing...',
        'save' => 'Save & Continue',
        'connected' => 'Connection successful!',
        'connect_failed' => 'Could not connect to the database: :error',
        'request_failed' => 'Request failed: :error',
    ],

    'settings' => [
        'title' => 'Settings',
        'intro' => 'Configure the remaining settings of the application. You can change them later in the .env file.',
    ],

    'admin' => [
        'title' => 'Administrator Account',
        'description' => 'This account will be created after the database is installed. Use it to log in.',
        'name' => 'Full Name',
        'email' => 'Email',
        'password' => 'Password',
        'confirm' => 'Confirm :field',
        'missing' => 'Administrator data is missing, please go back to the account step.',
        'identifier_missing' => 'Admin identifier column [:column] is not one of the admin fields.',
        'ready' => ':title :id is ready.',
    ],

    'run' => [
        'title' => 'Install',
        'intro' => 'Everything is ready. Click Install Now and keep this page open until it finishes.',
        'install_now' => 'Install Now',
        'installing' => 'Installing...',
        'retry' => 'Retry',
        'pending' => 'Pending',
        'running' => 'Running...',
        'done' => 'Done',
        'warning' => 'Warning',
        'failed' => 'Failed',
        'server_error' => 'Server error (HTTP :status). Check storage/logs/laravel.log.',
        'request_failed' => 'Request failed: :error',
        'complete_title' => 'Installation Complete!',
        'complete_intro' => ':app has been installed successfully. The installer is now locked.',
        'admin_login' => 'Admin Login',
        'go_website' => 'Go to Website',
    ],

    'tasks' => [
        'environment' => 'Writing environment configuration',
        'key' => 'Generating application key',
        'migrate' => 'Running database migrations',
        'seed' => 'Seeding the database',
        'storage' => 'Creating storage link',
        'admin' => 'Creating :title',
        'commands' => 'Running post-install commands',
        'optimize' => 'Clearing caches',
        'finish' => 'Finishing installation',
    ],

    'messages' => [
        'env_set' => 'APP_ENV=:env, APP_DEBUG=:debug',
        'key_generated' => 'New application key generated.',
        'key_set' => 'Application key is set.',
        'migrated' => 'Migrations completed.',
        'seeded' => 'Database seeded.',
        'storage_exists' => 'public/storage already exists.',
        'symlink_disabled' => 'symlink() is disabled on this server. Create the public/storage link manually.',
        'storage_linked' => 'Storage link created.',
        'storage_failed' => 'Could not create storage link: :error',
        'commands_done' => 'Post-install commands completed.',
        'caches_cleared' => 'Caches cleared.',
        'caches_failed' => 'Could not clear all caches: :error',
        'lock_failed' => 'Could not write the lock file :path',
        'completed' => 'Installation completed.',
    ],

];
