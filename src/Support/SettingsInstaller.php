<?php

namespace OiLab\OiLaravelPublish\Support;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;
use Throwable;

/**
 * SettingsInstaller
 *
 * Seeds the package's default publish settings (the description renderer keys)
 * into the host application's key/value Setting model. Idempotent: existing keys
 * are left untouched and the installer no-ops gracefully when no Setting model
 * is present.
 */
class SettingsInstaller
{
    public function canInstall(): bool
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

    /**
     * @return list<string> The keys created during this run.
     */
    public function install(): array
    {
        if (! $this->canInstall()) {
            return [];
        }

        $model = $this->modelClass();
        $keyColumn = config('oi-laravel-publish.settings.key_column', 'key');
        $valueColumn = config('oi-laravel-publish.settings.value_column', 'value');

        /** @var array<string, string> $defaults */
        $defaults = config('oi-laravel-publish.settings.defaults', []);

        $created = [];

        foreach ($defaults as $key => $value) {
            if ($model::query()->where($keyColumn, $key)->exists()) {
                continue;
            }

            $model::query()->create([
                $keyColumn => $key,
                $valueColumn => $value,
            ]);

            $created[] = $key;
        }

        return $created;
    }

    /**
     * @return class-string<Model>|null
     */
    protected function modelClass(): ?string
    {
        return config('oi-laravel-publish.settings.model');
    }
}
