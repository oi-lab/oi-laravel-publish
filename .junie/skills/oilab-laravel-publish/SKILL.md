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
  `sort`, `is_active`. Owns `cover` and `video` (single-file), `slides` and
  `gallery` attachment collections.
- **PublishTemplate** — a *static*, code-defined descriptor (NOT a database
  table). Each template has a `key`, `name`, `type` (`page` | `block`),
  default `props`, an optional typed `propsClass`, and (for page templates) an
  `allowedBlocks` list. Pages/blocks reference one by its string `template_key`.
- **PublishTemplateRegistry** — holds the catalogue, hydrated from
  `config('oi-laravel-publish.templates')`; extendable at runtime.
- **PropsData** — abstract base for typed props. `GenericPropsData` is the
  permissive fallback; typed block props live in `Data/Blocks/*` (HeroData,
  GridData, BlockquoteData, ContentData, FormData, SlidesData,
  BreadcrumbData, MapData, TableData, WarrantyData, FaqsData), and page props in
  `Data/Pages/PagePropsData` — one class shared by every page template.
- **ParamData** — a free-form `key` / `value` pair. `PagePropsData::$params` is
  an ordered list of them: the escape hatch for what a project alone knows about
  (tracking id, template variant, external reference).
- **PropsCast** — casts the JSON `props` column to the typed `PropsData`
  subclass declared by the row's template, falling back to `GenericPropsData`.
- **CtaData** — a call to action (`label`, `url`, `target`, `variant`, `size`,
  `position`). Every block but `breadcrumb` carries a `ctas` collection of them.
- **VideoData** — the video a `content` block plays in its media slot, in place
  of its cover (which then serves as its poster). Led by `source`: `youtube` /
  `vimeo` hold the `url` an author pasted, `library` holds nothing — the video is
  the file attached to the block's single-file `video` collection. `title` is how
  the player is announced; `autoplay`, `loop`, `muted` and `controls` reach the
  library player alone. `VideoSource::fromUrl()` says which platform an address
  belongs to, `SupportedVideoSource` refuses one meant for another, and
  `SilentAutoplay` refuses an audible autoplay. Nothing derived is stored: the
  video id is extracted, and the player built, by the host's frontend — a console
  previewing a block as it is typed has no server to ask.
- **`Data/Styles/*`** — presentation. Each block has its own `*StylesData`
  composing flat primitives (`BlockStyleData`, `HeadingStyleData`,
  `TextStyleData`, `CtasStyleData`, `QuoteStyleData`, `ListStyleData`,
  `CarouselStyleData`, `BreakpointsData`). Composed, never inherited.

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
    // PagePropsData: free-form key/value params, the only page-level prop
    'props' => ['params' => [['key' => 'gtm_id', 'value' => 'GTM-1234']]],
]);

$page->param('gtm_id');            // 'GTM-1234' (null-safe, takes a default)
$page->params();                   // ['gtm_id' => 'GTM-1234']
$page->props->hasParam('gtm_id');  // true — distinguishes absent from null

$child = PublishPage::create([
    'parent_id'    => $page->id,
    'template_key' => 'default',
    'name'         => 'About',
    'slug'         => 'about',  // slug is unique per parent, not globally
]);

$hero = PublishBlock::create([
    'publish_page_id' => $page->id,
    'template_key'    => 'hero',
    'name'            => 'Ship faster',   // the rendered title
    'key'             => 'hero',
    'excerpt'         => 'A short lead.',
    'props'           => [
        'pre'    => 'New',
        'ctas'   => [['label' => 'Get started', 'url' => '/signup']],
        'styles' => ['title' => ['align' => 'center']],
    ],
    'sort'            => 0,
]);

$page->blocks;                       // ordered by `sort`
$page->children;                     // ordered by `sort`
$hero->props;                        // HeroData instance (typed)
$hero->props->pre;                   // 'New'
$hero->props->ctas[0]->variant;      // CtaVariant::Default
$hero->props->styles->title->tag;    // HeadingTag::H2
$hero->template();                   // PublishTemplateData for 'hero'
```

`props` accepts either a raw array or a `PropsData` instance, and always reads
back as the typed `PropsData` resolved from the row's `template_key`.

## Typed props

A template's `propsClass` decides how `props` hydrates. To add a new typed
block, create a `Data/Blocks/<Name>Data` class **extending `PropsData`** and
point a template's `propsClass` at it in config. Unknown/typeless templates use
`GenericPropsData`, whose `->value('key', $default)` reads arbitrary keys.

Both bundled page templates share `Data/Pages/PagePropsData`: page props describe
the page as a whole, the same concern whatever the template. A project needing
more subclasses it and repoints the template's `propsClass`. `PublishPage::param()`
/ `params()` read the params either way, typed or from the generic bag.

**Props never carry content.** A block's title, lead and body are its `name`,
`excerpt` and `description` **columns**. Props hold what is specific to the
template (`pre`, `format`, `form_key`, `items`, …) plus the two cross-cutting
keys every block shares — `ctas` and `styles`. Never declare a `title`,
`excerpt` or `description` prop.

Every style field has a default, so a partial `styles` hydrates cleanly and
leaves the untouched siblings alone.

Run `php artisan publish:install-data --namespace="App\Publish"` to copy the
block, style and enum classes into the host application (namespaces rewritten,
`PropsData` still the package's). Afterwards, repoint each `templates.*.propsClass`
and swap `OiLab\OiLaravelPublish\Data` for the new namespace in `data_namespaces`
— keeping both aborts `oi:gen-ts` on a short-name collision.

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
- Content lives in columns, presentation and template specifics in `props`.
- `props` is typed via `PropsData` on the model. For TypeScript, add
  `OiLab\OiLaravelPublish\Data` to `data_namespaces` in `config/oi-laravel-ts.php`:
  `oi:gen-ts` then emits `IPublishPageData`, `IPublishBlockData`, the typed block
  interfaces (`IHeroData`, ...), `IPagePropsData`, `IParamData`, `ICtaData`, the
  `I*StylesData` interfaces, and `IPublishBlockData.props` as the union of the
  block props.
- Style classes are composed, never inherited: `oi-laravel-ts` reads only the
  constructor of the class it reflects, so an inherited property would silently
  vanish from the generated interface.
- Resolve models/templates through `OiLaravelPublish`, never hardcode `::class`.
- Override the `models`, `templates`, `attachments`, `renderers` and `settings`
  config to customise behaviour. Install `oi-lab/oi-laravel-settings` for
  zero-config, scoped/typed settings storage.

---

After changing the package's behaviour, update this file and run
`composer sync-ai-skills` (or `php artisan oi:install-ai-skill` in a host app).
