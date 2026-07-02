<?php

namespace OiLab\OiLaravelPublish\Stores;

use Illuminate\Support\Facades\Schema;
use OiLab\OiLaravelPublish\Contracts\SettingStore;
use OiLab\OiLaravelSettings\OiLaravelSettings;
use OiLab\OiLaravelSettings\SettingsManager;
use Throwable;

/**
 * First-class adapter backed by `oi-lab/oi-laravel-settings`. Used automatically
 * when that package is installed, so publish settings live in the shared,
 * scoped, typed Setting store instead of a bespoke key/value table.
 */
class OiLaravelSettingsStore implements SettingStore
{
    public function __construct(protected SettingsManager $settings) {}

    public function isAvailable(): bool
    {
        try {
            return Schema::hasTable(OiLaravelSettings::tableName());
        } catch (Throwable) {
            return false;
        }
    }

    public function get(string $key, ?string $default = null): ?string
    {
        $value = $this->settings->get($key, $default);

        return $value === null ? $default : (string) $value;
    }

    public function has(string $key): bool
    {
        return $this->settings->has($key);
    }

    public function set(string $key, string $value): void
    {
        $this->settings->set($key, $value, type: 'string', label: $key);
    }
}
