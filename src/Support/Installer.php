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

    public static function accessKey(): ?string
    {
        $key = config('installer.access_key');

        return filled($key) ? (string) $key : null;
    }

    public static function settingsGroups(): array
    {
        return config('installer.settings.groups') ?: [];
    }

    /**
     * Wizard steps that must be completed in order before installing.
     */
    public static function steps(): array
    {
        return array_values(array_filter([
            static::accessKey() ? 'access' : null,
            'requirements',
            'permissions',
            'database',
            static::settingsGroups() ? 'settings' : null,
            config('installer.admin.enabled') ? 'admin' : null,
        ]));
    }

    /**
     * Steps shown in the progress bar.
     */
    public static function stepLabels(): array
    {
        return array_filter([
            'welcome' => __('installer::installer.steps.welcome'),
            'requirements' => __('installer::installer.steps.requirements'),
            'permissions' => __('installer::installer.steps.permissions'),
            'database' => __('installer::installer.steps.database'),
            'settings' => static::settingsGroups()
                ? __(config('installer.settings.step_label') ?: 'installer::installer.steps.settings')
                : null,
            'admin' => config('installer.admin.enabled')
                ? __(config('installer.admin.step_label') ?: 'installer::installer.steps.admin')
                : null,
            'run' => __('installer::installer.steps.install'),
        ]);
    }

    /**
     * Normalize a list of configured form fields (admin or settings).
     */
    public static function fields(array $fields): array
    {
        $normalized = [];

        foreach ($fields as $key => $field) {
            $field = array_merge([
                'label' => ucwords(strtolower(str_replace('_', ' ', $key))),
                'type' => 'text',
                'rules' => 'nullable',
            ], $field);

            $field['label'] = __($field['label']);
            $field['hint'] = isset($field['hint']) ? __($field['hint']) : null;
            $field['rules'] = is_array($field['rules']) ? $field['rules'] : explode('|', $field['rules']);

            $normalized[$key] = $field;
        }

        return $normalized;
    }
}
