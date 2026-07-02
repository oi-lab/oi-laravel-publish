<?php

namespace OiLab\OiLaravelPublish\Support;

use OiLab\OiLaravelPublish\Contracts\SettingStore;
use OiLab\OiLaravelPublish\Stores\ConfigModelSettingStore;
use OiLab\OiLaravelPublish\Stores\OiLaravelSettingsStore;
use OiLab\OiLaravelSettings\SettingsManager;

/**
 * Resolves the active {@see SettingStore} implementation.
 *
 * Resolution order:
 *   1. an explicit store class bound via `oi-laravel-publish.settings.store`;
 *   2. the `oi-lab/oi-laravel-settings` adapter when that package is installed;
 *   3. the generic config-model store (backwards-compatible fallback).
 *
 * Config is read on every call so runtime overrides are always honoured.
 */
class SettingStoreFactory
{
    public static function make(): SettingStore
    {
        $explicit = config('oi-laravel-publish.settings.store');

        if (is_string($explicit) && $explicit !== '') {
            return app($explicit);
        }

        if (class_exists(SettingsManager::class)) {
            return app(OiLaravelSettingsStore::class);
        }

        return app(ConfigModelSettingStore::class);
    }
}
