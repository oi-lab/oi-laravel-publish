<?php

namespace OiLab\OiLaravelPublish\Support;

use OiLab\OiLaravelPublish\Contracts\SettingStore;

/**
 * SettingResolver
 *
 * Reads global publish settings through the active {@see SettingStore},
 * falling back to the package config defaults otherwise. Keeps the package
 * decoupled from any specific settings implementation while preferring
 * `oi-lab/oi-laravel-settings` when it is installed.
 */
class SettingResolver
{
    /**
     * @var array<string, string|null>
     */
    protected array $cache = [];

    public function get(string $key, ?string $default = null): ?string
    {
        if (! array_key_exists($key, $this->cache)) {
            $this->cache[$key] = SettingStoreFactory::make()->get($key);
        }

        return $this->cache[$key] ?? $default ?? $this->configDefault($key);
    }

    public function isAvailable(): bool
    {
        return SettingStoreFactory::make()->isAvailable();
    }

    protected function configDefault(string $key): ?string
    {
        $default = config('oi-laravel-publish.settings.defaults.'.$key);

        return $default === null ? null : (string) $default;
    }
}
