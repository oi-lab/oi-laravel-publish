---
title: Installation
description: Install the package, publish and run migrations, and seed settings.
order: 2
---

# Installation

## Install via Composer

```bash
composer require oi-lab/oi-laravel-publish
```

This also pulls in `oi-lab/oi-laravel-attachments` (media collections) and
`spatie/laravel-data` (typed props).

## Publish and run migrations

```bash
php artisan vendor:publish --tag=oi-laravel-publish-migrations
php artisan migrate
```

This creates the `publish_pages` and `publish_blocks` tables. The attachments
package contributes its own `files`, `folders` and `attachments` tables.

## Publish the configuration (optional)

```bash
php artisan vendor:publish --tag=oi-laravel-publish-config
```

Edit `config/oi-laravel-publish.php` to customise models, templates, attachment
collections, renderers, and the Setting integration.

## Seed settings (optional)

If your application exposes a key/value `Setting` model, seed the renderer
settings:

```bash
php artisan publish:install-settings
```

This is idempotent and no-ops gracefully when no `Setting` model is present. See
[Settings](../advanced/settings.md).
