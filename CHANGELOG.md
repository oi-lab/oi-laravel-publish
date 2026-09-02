# Changelog

All notable changes to `oi-lab/oi-laravel-publish` will be documented in this file.

## [2.0.0] - 2026-09-02

The major of the block styles. A block is no longer one flex column wearing one
`BlockStyleData`: it is a section holding three areas — header, body, footer —
each capped, placed and spaced on its own. **Existing block rows must be
rewritten**, and this time a migration ships: `php artisan publish:migrate-props`
carries every lot across, `--dry-run` first. The `features` template is renamed
`grid`, which no lot can do for you — it is the host's `template_key` column and
the host's `allowedBlocks` lists.

### Added
- **A video in the `content` block's media slot** — `VideoData`, carried by `ContentData` as its `video` prop, and a new single-file `video` attachment collection on `PublishBlock`. The video is led by its `source`: `youtube` / `vimeo` play an address the author pastes, `library` plays the file attached to the block. Given a cover as well, the block plays the video where the cover would have hung and the cover becomes its poster; `styles.media` places and shapes it exactly as it does the cover, except `background`, which a player cannot honour. Only what the author writes is stored: `VideoSource::fromUrl()` says which platform an address belongs to, `SupportedVideoSource` refuses one meant for another (or none at all), `SilentAutoplay` refuses a video asked to start on its own with sound, and the video id is extracted — and the embed address built — by the host's frontend, which has to resolve a player before anything is saved to preview a block as it is typed. The four playback options (`autoplay`, `loop`, `muted`, `controls`) reach the library player alone: a platform's player is the platform's. `oi:gen-ts` emits `IVideoData`, `IContentData` gains its `video` key, and `IPublishBlockData` its optional `video` attachment.
- **A block's media slot carries its own column** — `MediaStyleData` gains `width` (`BlockWidth`), `margin_x` (`BlockMarginX`) and `margin_y` (`BlockMarginY`), the same three an area carries. The figure was full-bleed and nothing else, so a video pasted into a `content` block spanned the section while the text beside it stopped at `md`; the cover and the video it shares the slot with can now be narrowed, placed across the section and spaced from what sits above and below. The defaults (`full` / `auto` / `none`) reproduce the figure as it was, so no stored block moves, and `layout: background` is unaffected — it is the full bleed. `oi:gen-ts` emits the three keys on `IMediaStyleData`.
- **`php artisan publish:migrate-props`** — the props of rows written before the style slots landed, rewritten one lot of renames at a time (`--lot=A`, `--dry-run`, `--chunk`). `PropsMigrator` holds the lots, `Props` reads and writes a dotted path in the stored JSON, and each `PropsLot` says what it moves: A and B the 1.2 props redesign, C the eleven templates split into areas, D the hero.
- **`BlockCapabilitiesData`** — what a block template actually renders (`pre`, `title`, `excerpt`, `body`, its media collections, `items`, `ctas`), declared on `PublishTemplateData` so a host console builds its editor from the registry instead of a map of its own. `BlockContract` reads the vocabulary of a repeated element off its `Data` class, and `BlockContract::styleSlots()` exposes a template's declared style slots by reflection, for a per-slot theme override.
- **`BlockSpaceX`**, and the `xs` / `xl` steps missing from `BlockWidth`, `BlockMarginY`, `BlockPaddingY` and `BlockSpaceY`. `BlockSpaceX` is the horizontal gap between a block's content and the media beside it.
- **`ItemMediaStyleData`, `MediaStyleData`, `TableStyleData`** as slots of their own, with the `MapProvider`, `TableBorders` and `TableDensity` enums.

### Fixed
- **A `body` call to action is drawn inside the body.** `CtaData::$position` names one of three areas, and `header` and `footer` landed in theirs — `body` did not: every block drew it as a sibling of its body, so an action an author had placed in the content sat outside the body area, outside its column, and outside any area at all. The host's renderer now hands the group to the body slot alongside the header's and the footer's. `hero` is the stated exception: it carries the unsplit `BlockStyleData` and has no three areas to place anything in, so its three groups follow one another in its single column.
- **A repeated element draws every call to action it carries.** `GridItemData`, `SlideItemData` and `StoryItemData` each hold a `ctas` collection whose entries have a `position`, but an element has one text column and none of a block's three areas — the renderer filtered them on `footer`, so an element CTA stored as `header` or `body` was silently dropped. Position is meaningless inside an element: all of them are drawn, wherever they were placed.

### Changed
- **A block is a section of three areas.** Every template but `hero`, `breadcrumb` and `reassurance` composes a `BlockAreaStyleData` for its header, its body and its footer — width, margin_x, padding_y, space_y, items, justify, each area's own — instead of sharing one `BlockStyleData` worn by the whole column. What belongs to the block itself (height, theme, its outer `margin_y`, and the gap between the three areas) moves to the new `BlockSectionStyleData`. **Existing rows must be rewritten**: `php artisan publish:migrate-props --lot=C` copies the old block-wide values into all three areas, which is what one shared column already rendered. `oi:gen-ts` emits `IBlockAreaStyleData` and `IBlockSectionStyleData`, and every `*StylesData` interface changes shape.
- **The item `Data` classes move to `Data/Items`.** `GridItemData`, `SlideItemData`, `StoryItemData`, `FaqItemData`, `WarrantyItemData` and `MapMarkerData` leave `Data/Blocks` — an element is not a block. Update any import naming them.
- **`BlockJustify::Evenly` is gone.** Nothing rendered it; props holding `evenly` no longer hydrate.
- **The `hero` carries the three structural areas.** It was the last template holding width, margin_x, padding_y, items, justify and an area's own space_y on one `BlockStyleData` worn by its whole column, so its overline, its title and its actions could never be placed apart. `HeroStylesData::$block` becomes a `BlockSectionStyleData` and the class gains `header_area`, `body_area` and `footer_area`, exactly as the eleven templates split by lot C did. **Existing hero rows must be rewritten**: run `php artisan publish:migrate-props --lot=D --dry-run`, read the diff, then drop the flag — the lot copies the block's six column fields to all three areas, which is what one shared column already rendered. A hero's *typography* is untouched: the host draws its overline, title, lead and body with its own components inside those areas. `oi:gen-ts` emits the new `IHeroStylesData` shape. `breadcrumb` and `reassurance` are now the only templates left on the unsplit slot.
- **The `features` block is now `grid`.** The block templated a grid of items, and only its name said "features" — a `grid` of `story`-shaped items reads for what it is, whatever a project fills it with. The template key `features` becomes `grid`, and its classes follow: `FeaturesData` → `GridData`, `FeatureItemData` → `GridItemData`, `FeaturesStylesData` → `GridStylesData`. `oi:gen-ts` emits `IGridData`, `IGridItemData` and `IGridStylesData` in their place. **Existing rows must be rewritten**: `update publish_blocks set template_key = 'grid' where template_key = 'features'`, and every `allowedBlocks` list naming `features` repointed. No migration ships with this release — the package does not own the host's table content.

## [1.8.0] - 2026-08-05

Two shapes change under existing rows: page props are typed from now on, and the `custom` block theme is gone. **Both need the stored props rewritten**; no migration ships with this release.

### Added
- **`Data/Pages/PagePropsData`** — page props are typed at last. Both bundled page templates (`default`, `landing`) now declare it as their `propsClass`, so `$page->props` hydrates to a `PagePropsData` instead of the permissive `GenericPropsData` bag. One class is shared by every page template: a page's props describe the page as a whole, which is the same concern whatever the template. A project needing more subclasses it and repoints the template's `propsClass`.
- **`ParamData`** — a free-form `key` / `value` pair, and `PagePropsData::$params`, the ordered list of them a page carries: the escape hatch for what a project alone knows about (a tracking id, a template variant, an external reference) without a migration or a typed field per need. `value` is a nullable string; params are edited as text and the host casts when it reads one.
- **`PublishPage::param()` / `params()`** and `PagePropsData::param()` / `hasParam()` / `paramsMap()` read a param by key. A missing param and one holding null both read as the default — `hasParam()` tells them apart; on a duplicate key the last occurrence wins. The model helpers also read the raw `params` list of a page whose template declares no `propsClass`, so a host template left on the generic bag keeps working.
- `PublishPageRequest` validates `props.params.*.key` (required, ≤255) and `props.params.*.value` (nullable, ≤2048); `PublishPageFactory::withParams(['key' => 'value'])` seeds them.
- `publish:install-data` now also copies `ParamData` and `Data/Pages/*` into the host application.
- **A `story` block** — `StoryData` (+ `StoryItemData`, `StoryStylesData`): a connected sequence of steps laid out along a central rail, each step carrying its own eyebrow, body, icon, cover and layout. The `story` template hydrated `WarrantyData` until now.
- **`PreStyleData`** — scale and alignment of the kicker above a title, added to `HeroStylesData` as its `pre` slot.
- **`PublishTemplateData::$requiresName`** — true by default; a template whose body already carries everything it renders sets it to false, and the console stops asking for a name it would never show.

### Changed
- **Page props are no longer a free-form bag.** A page on a bundled template only round-trips the keys `PagePropsData` declares — an existing row carrying arbitrary page props loses them on its next save. Move that content into `params`, or keep the loose shape by pointing the template's `propsClass` at a class of your own (or dropping it, which restores `GenericPropsData`).
- **`BlockTheme::Custom` is now `BlockTheme::System`.** The case hands the colour scheme back to the site's own setting, which `custom` suggested the opposite of. Props holding `custom` no longer hydrate.
- `oi:gen-ts` emits `IPagePropsData`, `IParamData`, `IStoryData`, `IStoryItemData`, `IStoryStylesData` and `IPreStyleData`; `IPublishPageData.props` becomes `IPagePropsData | Record<string, unknown>`, and the block props union gains the story and FAQ shapes — `IFaqsData` was missing from it.

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
