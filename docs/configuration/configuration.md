---
title: Configuration
description: The oi-laravel-publish config reference — models, templates, attachments, renderers, settings.
order: 1
---

# Configuration

Publish the config file:

```bash
php artisan vendor:publish --tag=oi-laravel-publish-config
```

## Keys

### `user_model`

Class used for the `created_by` / `updated_by` audit relationships.

### `models`

Override the package models with your own subclasses:

```php
'models' => [
    'page'  => \App\Models\Page::class,
    'block' => \App\Models\Block::class,
],
```

Always resolve them through `OiLaravelPublish::pageModel()` / `blockModel()`.

### `attachments`

The named attachment collections each model exposes and the upload limits used
by the form requests:

```php
'attachments' => [
    'page'  => ['cover'],
    'block' => ['cover', 'slides', 'gallery'],
    'max_files' => 30,
    'max_file_size' => 10240,
],
```

### `renderers`

Default renderer for the `description` field of pages and blocks. Overridden by
the `Setting` model when present.

```php
'renderers' => [
    'page'  => 'markdown',
    'block' => 'markdown',
],
```

### `settings`

Integration with a host key/value `Setting` model. See
[Settings](../advanced/settings.md).

```php
'settings' => [
    'model' => 'App\\Models\\Setting',
    'key_column' => 'key',
    'value_column' => 'value',
    'defaults' => [
        'PUBLISH.PAGE_DESCRIPTION_RENDERER' => 'markdown',
        'PUBLISH.BLOCK_DESCRIPTION_RENDERER' => 'markdown',
    ],
],
```

### `templates`

The catalogue of page and block [templates](../usage/templates.md). Each entry
maps to a `PublishTemplateData` (`key`, `name`, `type`, `description`, `props`,
`propsClass`, `allowedBlocks`).
