# Changelog

All notable changes to `oi-lab/oi-laravel-publish` will be documented in this file.

## [Unreleased]

### Added
- **`Data/Pages/PagePropsData`** — page props are typed at last. Both bundled page templates (`default`, `landing`) now declare it as their `propsClass`, so `$page->props` hydrates to a `PagePropsData` instead of the permissive `GenericPropsData` bag. One class is shared by every page template: a page's props describe the page as a whole, which is the same concern whatever the template. A project needing more subclasses it and repoints the template's `propsClass`.
- **`ParamData`** — a free-form `key` / `value` pair, and `PagePropsData::$params`, the ordered list of them a page carries: the escape hatch for what a project alone knows about (a tracking id, a template variant, an external reference) without a migration or a typed field per need. `value` is a nullable string; params are edited as text and the host casts when it reads one.
- **`PublishPage::param()` / `params()`** and `PagePropsData::param()` / `hasParam()` / `paramsMap()` read a param by key. A missing param and one holding null both read as the default — `hasParam()` tells them apart; on a duplicate key the last occurrence wins. The model helpers also read the raw `params` list of a page whose template declares no `propsClass`, so a host template left on the generic bag keeps working.
- `PublishPageRequest` validates `props.params.*.key` (required, ≤255) and `props.params.*.value` (nullable, ≤2048); `PublishPageFactory::withParams(['key' => 'value'])` seeds them.
- `publish:install-data` now also copies `ParamData` and `Data/Pages/*` into the host application.

### Changed
- **Page props are no longer a free-form bag.** A page on a bundled template only round-trips the keys `PagePropsData` declares — an existing row carrying arbitrary page props loses them on its next save. Move that content into `params`, or keep the loose shape by pointing the template's `propsClass` at a class of your own (or dropping it, which restores `GenericPropsData`).
- `oi:gen-ts` emits `IPagePropsData` and `IParamData`, and `IPublishPageData.props` as `IPagePropsData | Record<string, unknown>`.

## [1.2.0] - 2026-07-10

Every key of the JSON persisted in the `props` column changes shape. **Existing page and block rows must be rewritten**, and front-end components updated in lockstep. No migration ships with this release — the package is young enough that no production data was assumed.

### Added
- **`CtaData`** — a first-class call to action with `label`, `url`, `target` (`_self` / `_blank`), `variant` (`default` / `secondary` / `ghost` / `link`), `size` (`xs` / `sm` / `default` / `lg`) and a nullable `position` (`header` / `body` / `footer`). Every block but `breadcrumb` now carries a repeatable `ctas` collection, replacing the single, unstylable `cta_label` / `cta_url` pair. `SlideItemData` carries one singular `cta` instead, with no `position` to place it in.
- **`Data/Styles/*`** — presentation is now typed and per-block. Each block owns a `*StylesData` composing only the slots it can use, built from flat primitives: `BlockStyleData` (height, `items-*`, `justify-*`, theme), `HeadingStyleData` (tag `h1`–`h6`, alignment), `TextStyleData`, `CtasStyleData`, `QuoteStyleData`, `ListStyleData` (columns per breakpoint, ordered/unordered, marker), `CarouselStyleData`, and the responsive `BreakpointsData`. Every field has a default, so an absent or partial `styles` hydrates cleanly. The classes are composed and never inherited — `oi-laravel-ts` reflects only a class's own constructor, so an inherited property would vanish from the generated interface without warning.
- **13 backed enums** in `src/Enums/` (`HeadingTag`, `HorizontalAlign`, `BlockAlignItems`, `BlockJustify`, `BlockHeight`, `BlockTheme`, `CtaTarget`, `CtaVariant`, `CtaSize`, `CtaPosition`, `TextScale`, `ListType`, `ListMarker`), which `oi-laravel-ts` emits as TypeScript literal unions.
- **`MapMarkerData`** (`latitude`, `longitude`, `label`, `description`, `icon`) and `MapData::$markers`, replacing the single `marker_label`. `icon` names a file under `public/images/markers/`.
- **`php artisan publish:install-data`** copies the block, style and enum classes into the host application (`app/Publish/` by default), rewriting their namespaces so a project can adapt them without forking. The copies keep extending the package's `PropsData`, so `PropsCast` still hydrates them. Two steps remain manual: repointing each `templates.*.propsClass`, and swapping the package namespace out of `data_namespaces` in `config/oi-laravel-ts.php` — keeping both aborts `oi:gen-ts` on a short-name collision.

### Changed
- **Props no longer carry content.** A block's title, lead and body are its `name`, `excerpt` and `description` **columns**; the props duplicated them, leaving two candidate sources of truth for the same string. `title`, `excerpt` and `description` are gone from every props class. `pre` (the kicker above the title) stays a prop on `hero` and `warranty`, as does the table's `headers` / `rows` and the map's coordinates.

  | Block | Before | After |
  | --- | --- | --- |
  | `HeroData` | `heading`, `subheading`, `body`, `cta_label`, `cta_url` | `pre`, `ctas`, `styles` |
  | `FeaturesData` | `heading`, `intro`, `columns`, `items` | `items`, `ctas`, `styles` (columns → `styles.list.columns`) |
  | `WarrantyData` | `title`, `overview`, `items` | `pre`, `items`, `ctas`, `styles` |
  | `ContentData` | `body`, `format` | `format`, `ctas`, `styles` |
  | `BlockquoteData` | `quote`, `author`, `role`, `source_url` | `author`, `role`, `source_url`, `ctas`, `styles` |
  | `SlidesData` | `heading`, `items`, `autoplay`, … | `items`, `autoplay`, `interval`, `loop`, `ctas`, `styles` |
  | `TableData` | `caption`, `headers`, `rows` | `headers`, `rows`, `ctas`, `styles` |
  | `MapData` | `marker_label`, `zoom`, … | `markers`, `zoom`, `provider`, `ctas`, `styles` |
  | `FormData` | `heading`, `form_key`, … | `form_key`, `action`, `method`, `submit_label`, `success_message`, `ctas`, `styles` |
  | `BreadcrumbData` | `items` | `items`, `styles` (no `ctas`: it is navigation) |

  `BlockquoteData.quote` moves to the `description` column — the quote **is** the block's body. Collection items keep the shared vocabulary (`title` / `text` / `icon` / `url`); `WarrantyItemData` gained an `icon`, and `FeatureItemData` its own `ctas`.
- Template defaults in `config/oi-laravel-publish.php` now declare only what differs from the DTO defaults, instead of restating them.

### Fixed
- `PublishBlockFactory::warranty()` built its items with `label` / `description` while `WarrantyItemData` declared `title` / `text`, so reading `$block->props` on a factory-made warranty block threw `CannotCreateData`. The factory and the config defaults now match the DTOs.

## [1.1.0] - 2026-07-09

### Added
- `PublishPageData::fromModel()` and `PublishBlockData::fromModel()`. Spatie picks them up as magical creation methods, so `PublishPageData::from($page)` now works too, and `oi-laravel-ts` introspects the factory's first parameter to pair each DTO with its model (which is what `data_replaces_model` relies on).
- `PublishBlockData` carries `cover` and `slides`, `PublishPageData` carries `cover`, typed through `oi-laravel-attachments`' `AttachmentData`. Both are `Optional`: an absent key means the relation was not eager-loaded, where a `null` cover means it was loaded and there is none.
- `WarrantyData` joined the `props` union of `PublishBlockData`, so `IWarrantyData` is finally reachable from `IPublishBlockData.props`. The `warranty` template and its front-end component already existed.

### Fixed
- `PublishPageData::from($page)` and `PublishBlockData::from($block)` died with `TypeError: Argument #9 ($props) must be of type array, GenericPropsData given`. The model's `PropsCast` yields a typed `PropsData`, while the DTO expects the flat map. The new `fromModel()` factories flatten it through `toProps()`.

### Changed
- `PublishBlock::cover()` / `::slides()` and `PublishPage::cover()` now declare `MorphOne<Attachment, $this>` / `MorphMany<Attachment, $this>`, inherited from the corrected `HasAttachments` generics in `oi-laravel-attachments` 1.0.4.

> Note on the `props` union: its `array<string, mixed>` member widens to `Record<string, unknown>` in TypeScript, which absorbs every other member. The union carries no discriminant of its own, so the front end must narrow on `template_key` and cast — `props as IHeroData` once `template_key === 'hero'`.

## [1.0.5] - 2026-07-07

### Fixed
- Documentation: `docs/configuration/_index.md` was missing its YAML frontmatter, which dropped the page from the generated navigation and search index. Added `title`, `description`, and `order`.

## [1.0.4] - 2026-07-06

### Fixed
- Ship `docs/` in the Packagist dist so `doc:import` can discover the package's documentation.
- CI resolves `oi-lab` dependencies from Packagist instead of local path repositories.

## [1.0.3] - 2026-07-04

### Added
- A new `warranty` block type, and the block-scaffolding AI skill.

## [1.0.2] - 2026-07-02

### Added
- Pluggable `SettingStore` with an auto-wired `oi-laravel-settings` backend.

## [1.0.1] - 2026-07-01

### Changed
- Enriched README and added the GitHub preview image.

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
