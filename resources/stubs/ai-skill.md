# OI Laravel Publish — AI Context

This package is a content layer (CMS core) for Laravel. It models **recursive
pages** that each own an **ordered collection of blocks**, where the processing
and rendering of every page and block is described by a **code-defined
template**. Props are typed with `spatie/laravel-data` and are compatible with
`oi-lab/oi-laravel-ts`; media is handled by `oi-lab/oi-laravel-attachments`.

The package ships the data layer only — models, data, form requests, migrations,
config and the template registry. It does **not** ship controllers, routes or
views; the host application wires the UI (e.g. Inertia/React).

## Core concepts

- **PublishPage** — a recursive page (`parent_id` → `children`), with `name`,
  `slug`, `excerpt`, `description`, typed `props`, `template_key`, `sort`,
  `is_active`. Owns a single `cover` attachment and a `blocks()` collection.
- **PublishBlock** — an ordered block belonging to one page (`publish_page_id`),
  with `name`, `key`, `excerpt`, `description`, typed `props`, `template_key`,
  `sort`, `is_active`. Owns `cover` and `slides` attachment collections.
- **PublishTemplate** — a *static*, code-defined descriptor (NOT a database
  table). Each template has a `key`, `name`, `type` (`page` | `block`),
  default `props`, an optional typed `propsClass`, and (for page templates) an
  `allowedBlocks` list. Pages/blocks reference one by its string `template_key`.
- **PublishTemplateRegistry** — holds the catalogue, hydrated from
  `config('oi-laravel-publish.templates')`; extendable at runtime.
- **PropsData** — abstract base for typed props. `GenericPropsData` is the
  permissive fallback; typed block props live in `Data/Blocks/*` (HeroData,
  FeaturesData, BlockquoteData, ContentData, FormData, SlidesData,
  BreadcrumbData, MapData, TableData).
- **PropsCast** — casts the JSON `props` column to the typed `PropsData`
  subclass declared by the row's template, falling back to `GenericPropsData`.

## The static resolver

Always resolve collaborators through `OiLab\OiLaravelPublish\OiLaravelPublish`:

```php
OiLaravelPublish::pageModel();             // configurable model class
OiLaravelPublish::blockModel();
OiLaravelPublish::userModel();
OiLaravelPublish::registry();              // PublishTemplateRegistry
OiLaravelPublish::template('hero');        // ?PublishTemplateData
OiLaravelPublish::pageTemplates();         // array<string, PublishTemplateData>
OiLaravelPublish::blockTemplates();
OiLaravelPublish::pageDescriptionRenderer();   // 'markdown' (Setting-aware)
OiLaravelPublish::blockDescriptionRenderer();
```

## Working with pages and blocks

```php
use OiLab\OiLaravelPublish\Models\PublishPage;
use OiLab\OiLaravelPublish\Models\PublishBlock;

$page = PublishPage::create([
    'template_key' => 'landing',
    'name'  => 'Home',
    'slug'  => 'home',
    'props' => [],            // GenericPropsData (default template has no propsClass)
]);

$child = PublishPage::create([
    'parent_id'    => $page->id,
    'template_key' => 'default',
    'name'         => 'About',
    'slug'         => 'about',  // slug is unique per parent, not globally
]);

$hero = PublishBlock::create([
    'publish_page_id' => $page->id,
    'template_key'    => 'hero',
    'name'            => 'Hero',
    'key'             => 'hero',
    'props'           => ['heading' => 'Welcome', 'alignment' => 'center'],
    'sort'            => 0,
]);

$page->blocks;            // ordered by `sort`
$page->children;         // ordered by `sort`
$hero->props;            // HeroData instance (typed)
$hero->props->heading;   // 'Welcome'
$hero->template();       // PublishTemplateData for 'hero'
```

`props` accepts either a raw array or a `PropsData` instance, and always reads
back as the typed `PropsData` resolved from the row's `template_key`.

## Typed props

A template's `propsClass` decides how `props` hydrates. To add a new typed
block, create a `Data/Blocks/<Name>Data` class **extending `PropsData`** and
point a template's `propsClass` at it in config. Unknown/typeless templates use
`GenericPropsData`, whose `->value('key', $default)` reads arbitrary keys.

## Attachments

Pages and blocks use `oi-lab/oi-laravel-attachments`:

```php
$page->attachFile($file, 'cover');           // single cover
$page->cover;                                 // MorphOne attachment

$block->syncAttachments([$a, $b], 'slides');  // ordered carousel images
$block->slides()->get();                      // ordered slides
$block->attachFile($file, 'cover');
```

Use `OiLab\OiLaravelAttachments\Actions\AttachUploadedFiles::handle($model, $request->file('slides') ?? [], 'slides')`
to store and attach uploads in one step.

## Form requests

`PublishPageRequest` and `PublishBlockRequest` validate input, including
`template_key` against the registry (page vs block templates) and `cover` /
`slides` uploads.

## Settings

Settings are read/written through a pluggable `OiLab\OiLaravelPublish\Contracts\SettingStore`.
Resolution order: explicit `config('oi-laravel-publish.settings.store')` class →
`oi-lab/oi-laravel-settings` adapter when that package is installed (recommended,
auto-wired, listed under `suggest`) → generic key/value `Setting` model
(`settings.model`) → no-op. `SettingResolver` / `SettingsInstaller` are thin façades
over the resolved store.

Run `php artisan publish:install-settings` to seed:

- `PUBLISH.PAGE_DESCRIPTION_RENDERER` (default `markdown`)
- `PUBLISH.BLOCK_DESCRIPTION_RENDERER` (default `markdown`)

Everything no-ops gracefully and falls back to `config('oi-laravel-publish.renderers')`
when no store is available.

## Conventions

- Recursive pages only; blocks are a flat ordered list per page.
- `props` is typed via `PropsData` on the model. For TypeScript, add
  `OiLab\OiLaravelPublish\Data` to `data_namespaces` in `config/oi-laravel-ts.php`:
  `oi:gen-ts` then emits `IPublishPageData`, `IPublishBlockData`, the typed block
  interfaces (`IHeroData`, ...), and `IPublishBlockData.props` as their union.
- Resolve models/templates through `OiLaravelPublish`, never hardcode `::class`.
- Override the `models`, `templates`, `attachments`, `renderers` and `settings`
  config to customise behaviour. Install `oi-lab/oi-laravel-settings` for
  zero-config, scoped/typed settings storage.

---

After changing the package's behaviour, update this file and run
`composer sync-ai-skills` (or `php artisan oi:install-ai-skill` in a host app).
