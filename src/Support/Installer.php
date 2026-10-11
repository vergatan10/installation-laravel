<?php

namespace Vergatan10\Installer\Support;

class Installer
{
    public static function lockPath(): string
    {
        return storage_path(config('installer.lock_file', 'installed'));
    }

    public static function isInstalled(): bool
    {
        return is_file(static::lockPath());
    }

    public static function markInstalled(): bool
    {
        $payload = json_encode([
            'installed_at' => date('c'),
            'app_version' => app()->version(),
        ], JSON_PRETTY_PRINT);

        return file_put_contents(static::lockPath(), $payload) !== false;
    }

    public static function unlock(): bool
    {
        return ! static::isInstalled() || @unlink(static::lockPath());
    }
}
