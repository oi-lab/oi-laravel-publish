# OI Laravel Publish

Use the `oi-laravel-publish` package to build a CMS content layer: recursive
`PublishPage` models that own an ordered collection of `PublishBlock`s, each
referencing a code-defined `PublishTemplate` (resolved through the
`PublishTemplateRegistry`). Block/page `props` are typed with spatie/laravel-data
via `PropsData` + `PropsCast` (oi-laravel-ts compatible), and media uses the
`oi-laravel-attachments` `cover`/`slides` collections. Resolve models and
templates through `OiLab\OiLaravelPublish\OiLaravelPublish`, and seed renderer
settings with `php artisan publish:install-settings`.

- IMPORTANT: Activate `oilab-laravel-publish` when modelling CMS pages, blocks,
  page/block templates, or typed `props` content in this Laravel application.
