# Changelog

All notable changes to `oi-lab/oi-laravel-publish` will be documented in this file.

## [1.0.0] - 2026-06-30

### Added

- `PublishPage` model: recursive pages (`parent_id`/`children`), ordered
  `blocks`, slug unique per parent, soft deletes, audit columns, `cover`
  attachment collection.
- `PublishBlock` model: ordered blocks belonging to a page, `cover` and `slides`
  attachment collections.
- Code-defined templates via `PublishTemplateData`, `PublishTemplateType`
  (page/block) and the `PublishTemplateRegistry`, with a bundled catalogue
  (`default`, `landing` pages; `hero`, `features`, `blockquote`, `content`,
  `form`, `slides`, `breadcrumb`, `map`, `table` blocks).
- Typed `props` via `PropsCast` + `PropsData` (abstract) / `GenericPropsData`
  fallback and per-template typed classes in `Data/Blocks/*`, compatible with
  `spatie/laravel-data` and `oi-lab/oi-laravel-ts`.
- `OiLaravelPublish` static resolver for models, templates and renderers.
- `PublishPageRequest` / `PublishBlockRequest` form requests.
- `Setting` model integration (`publish:install-settings`) seeding
  `PUBLISH.PAGE_DESCRIPTION_RENDERER` and `PUBLISH.BLOCK_DESCRIPTION_RENDERER`.
- Migrations, factories, config, documentation and AI assistant skill.
- 35 Pest/Testbench tests (PHP 8.2–8.4, Laravel 11–13).
