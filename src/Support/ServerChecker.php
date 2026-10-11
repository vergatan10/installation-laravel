<?php

namespace Vergatan10\Installer\Support;

class ServerChecker
{
    public function requirements(): array
    {
        $minPhp = config('installer.requirements.php', '8.1.0');

        $items = [[
            'label' => 'PHP >= '.$minPhp,
            'current' => PHP_VERSION,
            'ok' => version_compare(PHP_VERSION, $minPhp, '>='),
        ]];

        foreach (config('installer.requirements.extensions', []) as $extension) {
            $items[] = [
                'label' => strtoupper($extension).' extension',
                'current' => extension_loaded($extension) ? 'Enabled' : 'Missing',
                'ok' => extension_loaded($extension),
            ];
        }

        return $items;
    }

    public function permissions(): array
    {
        $items = [];

        foreach (config('installer.permissions', []) as $path) {
            $full = base_path($path);

            // A missing .env is fine as long as the project root is writable.
            $ok = file_exists($full) ? is_writable($full) : is_writable(dirname($full));

            $items[] = [
                'label' => $path,
                'current' => file_exists($full) ? substr(sprintf('%o', fileperms($full)), -3) : 'not found',
                'ok' => $ok,
            ];
        }

        return $items;
    }

    public static function passes(array $items): bool
    {
        foreach ($items as $item) {
            if (! $item['ok']) {
                return false;
            }
        }

        return true;
    }
}
