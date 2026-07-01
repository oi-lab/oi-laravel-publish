<?php

namespace OiLab\OiLaravelPublish\Support;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;
use Throwable;

/**
 * SettingResolver
 *
 * Reads global publish settings from the host application's key/value Setting
 * model when available, falling back to the package config defaults otherwise.
 * Keeps the package decoupled from any specific settings implementation.
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
            $this->cache[$key] = $this->fetch($key);
        }

        return $this->cache[$key] ?? $default ?? $this->configDefault($key);
    }

    public function isAvailable(): bool
    {
        $model = $this->modelClass();

        if ($model === null || ! class_exists($model)) {
            return false;
        }

        try {
            return Schema::hasTable((new $model)->getTable());
        } catch (Throwable) {
            return false;
        }
    }

    protected function fetch(string $key): ?string
    {
        if (! $this->isAvailable()) {
            return null;
        }

        $model = $this->modelClass();
        $keyColumn = config('oi-laravel-publish.settings.key_column', 'key');
        $valueColumn = config('oi-laravel-publish.settings.value_column', 'value');

        try {
            /** @var Model|null $record */
            $record = $model::query()->where($keyColumn, $key)->first();
        } catch (Throwable) {
            return null;
        }

        $value = $record?->getAttribute($valueColumn);

        return $value === null ? null : (string) $value;
    }

    /**
     * @return class-string<Model>|null
     */
    protected function modelClass(): ?string
    {
        return config('oi-laravel-publish.settings.model');
    }

    protected function configDefault(string $key): ?string
    {
        $default = config('oi-laravel-publish.settings.defaults.'.$key);

        return $default === null ? null : (string) $default;
    }
}
