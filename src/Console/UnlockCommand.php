<?php

namespace Vergatan10\Installer\Console;

use Vergatan10\Installer\Support\Installer;
use Illuminate\Console\Command;

class UnlockCommand extends Command
{
    protected $signature = 'installer:unlock {--force : Skip the confirmation}';

    protected $description = 'Remove the installed lock so the web installer becomes reachable again';

    public function handle(): int
    {
        if (! Installer::isInstalled()) {
            $this->info('Application is not locked.');

            return self::SUCCESS;
        }

        if (! $this->option('force')
            && ! $this->confirm('Anyone visiting /'.config('installer.route_prefix').' could then reconfigure this app. Continue?')) {
            return self::FAILURE;
        }

        Installer::unlock();
        $this->warn('Installer unlocked. Run `php artisan installer:lock` to lock it again.');

        return self::SUCCESS;
    }
}
