# FeatureItem: per-item cover + ratio

**Date:** 2026-07-14
**Package:** `oi-lab/oi-laravel-publish`
**Status:** Approved design, ready for implementation plan

## Problem

The "features" block renders a grid of `FeatureItemData` (title, text, icon, ctas).
A feature item cannot carry its own image today: the block owns a single
block-level `cover` attachment (arranged by `cover_layout` / `cover_ratio`), which
is shared, not per-item. We want each feature item to optionally show **its own
cover image at its own aspect ratio**.

This mirrors the slide work already shipped (`SlideItemData.attachment_uuid` +
`SlidesData.media_ratio`): a per-item attachment referenced by a stable uuid, plus
a per-item ratio typed by the shared `MediaRatio` enum.

## Decisions

| Question | Decision |
| --- | --- |
| Cover source | A **new, dedicated `gallery` attachment collection** on blocks (alongside `cover` and `slides`). Each feature item links one `gallery` entry. Self-documenting, zero migration (a collection is a string filter on the attachments table), and keeps the block-level `cover` distinct. |
| Item → cover link | **`?string $attachment_uuid`** on `FeatureItemData`, referencing an `Attachment.uuid` in the block's `gallery` collection. Same name/mechanism as `SlideItemData`. `null` = item with no image. |
| Ratio | **`MediaRatio $cover_ratio`** on `FeatureItemData`, default `MediaRatio::Inherit` (defer to the block's `cover_ratio` / theme). Reuses the shared enum. Field named `cover_ratio` to match the block-level ratio. |
| Validation | Bounded, nullable string on the Data attribute (`#[Nullable, Max(36)]`). Referential integrity (the uuid belongs to this block's `gallery`) is a host concern, resolved when persisting/rendering — consistent with slides. |

## Design

### 1. New `gallery` attachment collection (block-level)

- `config/oi-laravel-publish.php`: `'block' => ['cover', 'slides', 'gallery']`.
- `PublishBlock::gallery(): MorphMany` — `return $this->attachments('gallery');`,
  mirroring `slides()`. Add the `@property-read Collection<int, Attachment> $gallery`
  line to the model docblock.
- `PublishBlockData`: add `public array|Optional $gallery = new Optional`, populated
  in `fromModel()` exactly like `slides` (present only when the relation is loaded;
  each entry mapped through `Attachment::toData()`). Update the class docblock's
  `Optional` note to mention `gallery` alongside `cover`/`slides`.

### 2. `FeatureItemData` — cover + ratio

```php
class FeatureItemData extends Data
{
    /**
     * @param  CtaData[]  $ctas
     */
    public function __construct(
        #[Required, Max(255)]
        public string $title,
        #[Nullable]
        public ?string $text = null,
        #[Nullable, Max(255)]
        public ?string $icon = null,
        #[Nullable, Max(36)]
        public ?string $attachment_uuid = null,
        public MediaRatio $cover_ratio = MediaRatio::Inherit,
        #[DataCollectionOf(CtaData::class)]
        public array $ctas = [],
    ) {}
}
```

- `attachment_uuid === null` → feature item with no cover.
- `cover_ratio === MediaRatio::Inherit` → defer to the block's `cover_ratio`
  (`FeaturesData.cover_ratio`), then the theme.
- Add/extend the class docblock: an item is potentially text (`title`, `text`),
  an icon, an optional cover (`attachment_uuid` → the block's `gallery`
  collection, resolved by uuid not position, host-enforced) at its own
  `cover_ratio`, and calls to action.

### 3. Validation & host contract

- `FeatureItemData::$attachment_uuid` validates `#[Nullable, Max(36)]`. Not
  enforced in `PublishBlockRequest` (it treats `props` opaquely, and referential
  integrity is uncheckable at create time — gallery uploads receive their uuid
  only after `AttachUploadedFiles::handle` runs).
- `PublishBlockRequest`: add the upload rules for the new collection, mirroring
  `slides`:
  - `'gallery' => ['nullable', 'array', "max:{$maxFiles}"]`
  - `'gallery.*' => ['file', 'image', "max:{$maxSize}"]`
- Front-end (host): resolve each feature item's image by
  `items[].attachment_uuid` against the serialized `gallery[]`; render text/icon
  only when null; apply `cover_ratio` (falling back to the block's `cover_ratio`
  when `Inherit`).

### 4. Peripheral scope

- `PublishBlockFactory` — **not modified**: no `features()` state exists (feature
  blocks are built via `PublishBlock::factory()->template('features')`). The new
  fields are covered by unit tests on `FeatureItemData::from([...])` plus a
  round-trip through a `template('features')` block persisted with `props.items`
  carrying `attachment_uuid` / `cover_ratio`.
- Docs: `docs/usage/blocks.md` (features section — mention per-item cover +
  `cover_ratio`, sourced from the `gallery` collection) and
  `docs/usage/attachments.md` (new `gallery` collection on blocks).
- `composer sync-ai-skills` after the behaviour change.
- Host-side `oi:gen-ts` regen (new `attachment_uuid` / `cover_ratio` on
  `IFeatureItemData`, new `gallery` on `IPublishBlockData`) — out of package scope.

## Impact & blast radius

- **Package only** — no host copy exists (`App\Publish` namespace absent).
- Touches: `config/oi-laravel-publish.php`, `PublishBlock`, `PublishBlockData`,
  `FeatureItemData`, `PublishBlockRequest`, docs, factory (if applicable), tests.
- No migration (collection names are data, not schema).
- Non-breaking: `FeatureItemData` gains two optional constructor params with
  defaults; `FeaturesData` and existing feature items are unaffected.

## Out of scope

- Per-item `cover_layout` (features items get a ratio only; layout stays a
  block-level concern for now).
- Removing or changing the block-level `cover` / `cover_layout` / `cover_ratio`
  on `FeaturesData`.
- Extending per-item covers to other item types (`SlideItemData` already has its
  own via `slides`; `WarrantyItemData`, `FaqItemData`, etc. are untouched).
- Front-end/Inertia rendering (host concern; this package ships the data layer).
- The `gallery` collection is scoped to feature-item covers only. Do not overload
  it for per-item covers on other block types — add a purpose-named collection
  instead, to preserve the one-collection-per-purpose clarity `slides` has.
