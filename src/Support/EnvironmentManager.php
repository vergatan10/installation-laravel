<?php

namespace Vergatan10\Installer\Support;

class EnvironmentManager
{
    public function __construct(protected string $path)
    {
    }

    public function path(): string
    {
        return $this->path;
    }

    public function exists(): bool
    {
        return is_file($this->path);
    }

    public function isWritable(): bool
    {
        return $this->exists() ? is_writable($this->path) : is_writable(dirname($this->path));
    }

    /**
     * Create the .env file from .env.example (or empty) when it is missing.
     */
    public function ensureExists(?string $example = null): bool
    {
        if ($this->exists()) {
            return true;
        }

        if (! is_writable(dirname($this->path))) {
            return false;
        }

        $content = ($example && is_file($example)) ? file_get_contents($example) : '';

        return file_put_contents($this->path, $content) !== false;
    }

    public function get(string $key): ?string
    {
        if (! $this->exists()) {
            return null;
        }

        if (preg_match('/^\s*'.preg_quote($key, '/').'\s*=(.*)$/m', file_get_contents($this->path), $m)) {
            return trim(trim($m[1]), '"\'');
        }

        return null;
    }

    /**
     * Set (or add) keys in the .env file. Commented-out keys such as
     * "# DB_HOST=127.0.0.1" are uncommented and replaced.
     */
    public function set(array $values): bool
    {
        if (! $this->isWritable()) {
            return false;
        }

        $content = $this->exists() ? file_get_contents($this->path) : '';

        foreach ($values as $key => $value) {
            $line = $key.'='.$this->format($value);
            $pattern = '/^[ \t]*#?[ \t]*'.preg_quote($key, '/').'[ \t]*=.*$/m';

            if (preg_match($pattern, $content)) {
                $content = preg_replace_callback($pattern, fn () => $line, $content, 1);
            } else {
                $content = rtrim($content, "\r\n").PHP_EOL.$line.PHP_EOL;
            }
        }

        return file_put_contents($this->path, $content, LOCK_EX) !== false;
    }

    protected function format(mixed $value): string
    {
        if (is_bool($value)) {
            return $value ? 'true' : 'false';
        }

        $value = (string) $value;

        if ($value === '' || preg_match('/^[A-Za-z0-9_.:\/@+\-]+$/', $value)) {
            return $value;
        }

        // Single quotes are literal in phpdotenv (no ${VAR} interpolation).
        if (! str_contains($value, "'")) {
            return "'".$value."'";
        }

        return '"'.addcslashes($value, '"\\').'"';
    }
}
