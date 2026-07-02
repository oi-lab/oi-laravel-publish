---
title: Settings
description: Seed and resolve PUBLISH renderer settings through a pluggable SettingStore.
order: 1
---

# Settings

The package seeds and resolves the description renderer settings through a
pluggable `SettingStore`. It never hard-depends on a concrete settings
implementation: it prefers `oi-lab/oi-laravel-settings` when installed, falls
back to a generic key/value model otherwise, and no-ops gracefully when nothing
is available (using `config('oi-laravel-publish.renderers')`).

## Seeded keys

| Key | Default |
|-----|---------|
| `PUBLISH.PAGE_DESCRIPTION_RENDERER` | `markdown` |
| `PUBLISH.BLOCK_DESCRIPTION_RENDERER` | `markdown` |

## Choosing the backend

The store is resolved on every call, in this order:

1. **Explicit** — a class bound via `config('oi-laravel-publish.settings.store')`.
2. **oi-laravel-settings** — used automatically when the package is installed
   (recommended). Values are stored scoped and typed in the shared Setting store.
3. **Config model** — the generic key/value fallback (`settings.model` +
   `key_column` / `value_column`), for hosts with their own `Setting` table.

`oi-lab/oi-laravel-settings` is listed in the package's `suggest`. Install it for
zero-config, first-class settings:

```bash
composer require oi-lab/oi-laravel-settings
```

## Installing

```bash
php artisan publish:install-settings
```

The installer is idempotent — existing keys are left untouched.

## Resolving at runtime

```php
use OiLab\OiLaravelPublish\OiLaravelPublish;

OiLaravelPublish::pageDescriptionRenderer();   // Setting value, else config default
OiLaravelPublish::blockDescriptionRenderer();
```

Under the hood `SettingResolver` and `SettingsInstaller` are thin façades over
the resolved `SettingStore`, caching lookups per request and falling back to the
config default otherwise.

## Custom store

Implement `OiLab\OiLaravelPublish\Contracts\SettingStore` and point the config at
it (a class name or container binding key):

```php
'settings' => [
    'store' => \App\Settings\MyPublishStore::class,
],
```
