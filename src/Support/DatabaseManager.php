<?php

namespace Vergatan10\Installer\Support;

use Illuminate\Support\Facades\DB;
use PDO;
use Throwable;

class DatabaseManager
{
    /**
     * Try to connect with the given credentials. Returns null on success,
     * otherwise the error message.
     */
    public function test(array $data): ?string
    {
        $name = 'installer_test';

        try {
            if ($data['connection'] === 'sqlite') {
                $this->ensureSqliteFile($data['database']);
            }

            config(["database.connections.{$name}" => $this->connectionConfig($data)]);
            DB::purge($name);
            DB::connection($name)->getPdo();
            DB::connection($name)->select('select 1');

            return null;
        } catch (Throwable $e) {
            return $e->getMessage();
        } finally {
            DB::purge($name);
        }
    }

    public function connectionConfig(array $data): array
    {
        $driver = $data['connection'];
        $base = config("database.connections.{$driver}") ?? ['driver' => $driver];

        if ($driver === 'sqlite') {
            return array_merge($base, ['driver' => 'sqlite', 'database' => $data['database']]);
        }

        return array_merge($base, [
            'driver' => $base['driver'] ?? $driver,
            'host' => $data['host'],
            'port' => $data['port'],
            'database' => $data['database'],
            'username' => $data['username'],
            'password' => $data['password'] ?? '',
            'options' => ($base['options'] ?? []) + [PDO::ATTR_TIMEOUT => 5],
        ]);
    }

    public function envValues(array $data): array
    {
        if ($data['connection'] === 'sqlite') {
            return [
                'DB_CONNECTION' => 'sqlite',
                'DB_DATABASE' => $data['database'],
            ];
        }

        return [
            'DB_CONNECTION' => $data['connection'],
            'DB_HOST' => $data['host'],
            'DB_PORT' => $data['port'],
            'DB_DATABASE' => $data['database'],
            'DB_USERNAME' => $data['username'],
            'DB_PASSWORD' => $data['password'] ?? '',
        ];
    }

    protected function ensureSqliteFile(string $path): void
    {
        if (! is_file($path)) {
            if (! is_dir(dirname($path))) {
                mkdir(dirname($path), 0755, true);
            }
            touch($path);
        }
    }
}
