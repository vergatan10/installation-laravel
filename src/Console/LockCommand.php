<?php

namespace Vergatan10\Installer\Console;

use Vergatan10\Installer\Support\Installer;
use Illuminate\Console\Command;

class LockCommand extends Command
{
    protected $signature = 'installer:lock';

    protected $description = 'Mark the application as installed (disables the web installer)';

    public function handle(): int
    {
        if (Installer::isInstalled()) {
            $this->info('Application is already marked as installed.');

            return self::SUCCESS;
        }

        if (! Installer::markInstalled()) {
            $this->error('Could not write '.Installer::lockPath());

            return self::FAILURE;
        }

        $this->info('Installer locked: '.Installer::lockPath());

        return self::SUCCESS;
    }
}
