---
title: Settings
description: Seed and resolve PUBLISH renderer settings from a host Setting model.
order: 1
---

# Settings

When the host application exposes a key/value `Setting` model, the package can
seed and resolve the description renderer settings from it. Everything no-ops
gracefully and falls back to `config('oi-laravel-publish.renderers')` when no
model is present.

## Seeded keys

| Key | Default |
|-----|---------|
| `PUBLISH.PAGE_DESCRIPTION_RENDERER` | `markdown` |
| `PUBLISH.BLOCK_DESCRIPTION_RENDERER` | `markdown` |

## Installing

```bash
php artisan publish:install-settings
```

The installer is idempotent — existing keys are left untouched. Configure the
model and column names under `config('oi-laravel-publish.settings')`.

## Resolving at runtime

```php
use OiLab\OiLaravelPublish\OiLaravelPublish;

OiLaravelPublish::pageDescriptionRenderer();   // Setting value, else config default
OiLaravelPublish::blockDescriptionRenderer();
```

Under the hood `SettingResolver` reads the `Setting` model when its table exists,
caching lookups per request, and falls back to the config default otherwise.
