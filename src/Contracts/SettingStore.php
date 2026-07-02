<?php

namespace OiLab\OiLaravelPublish\Contracts;

/**
 * Bridge to the host application's settings storage.
 *
 * The package never assumes a concrete implementation: it reads and seeds its
 * settings through this contract. When `oi-lab/oi-laravel-settings` is installed
 * it is wired automatically; otherwise the package falls back to a generic
 * key/value Eloquent model resolved from config.
 */
interface SettingStore
{
    /**
     * Whether the underlying storage is present and usable.
     */
    public function isAvailable(): bool;

    /**
     * Resolve a setting value as a string, or the given default when absent.
     */
    public function get(string $key, ?string $default = null): ?string;

    /**
     * Whether a value is stored for the given key.
     */
    public function has(string $key): bool;

    /**
     * Create or update a setting value.
     */
    public function set(string $key, string $value): void;
}
