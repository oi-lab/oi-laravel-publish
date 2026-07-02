<?php

namespace OiLab\OiLaravelPublish\Stores;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;
use OiLab\OiLaravelPublish\Contracts\SettingStore;
use Throwable;

/**
 * Reads and writes settings against a generic key/value Eloquent model resolved
 * from config (`oi-laravel-publish.settings.model`). This is the fallback used
 * when `oi-lab/oi-laravel-settings` is not installed, keeping the package usable
 * with any host-owned Setting table.
 */
class ConfigModelSettingStore implements SettingStore
{
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

    public function get(string $key, ?string $default = null): ?string
    {
        if (! $this->isAvailable()) {
            return $default;
        }

        try {
            /** @var Model|null $record */
            $record = $this->modelClass()::query()->where($this->keyColumn(), $key)->first();
        } catch (Throwable) {
            return $default;
        }

        $value = $record?->getAttribute($this->valueColumn());

        return $value === null ? $default : (string) $value;
    }

    public function has(string $key): bool
    {
        if (! $this->isAvailable()) {
            return false;
        }

        return $this->modelClass()::query()->where($this->keyColumn(), $key)->exists();
    }

    public function set(string $key, string $value): void
    {
        if (! $this->isAvailable()) {
            return;
        }

        $this->modelClass()::query()->updateOrCreate(
            [$this->keyColumn() => $key],
            [$this->valueColumn() => $value],
        );
    }

    /**
     * @return class-string<Model>|null
     */
    protected function modelClass(): ?string
    {
        return config('oi-laravel-publish.settings.model');
    }

    protected function keyColumn(): string
    {
        return config('oi-laravel-publish.settings.key_column', 'key');
    }

    protected function valueColumn(): string
    {
        return config('oi-laravel-publish.settings.value_column', 'value');
    }
}
