# Slides: optional per-slide attachments + block display format

**Date:** 2026-07-14
**Package:** `oi-lab/oi-laravel-publish`
**Status:** Approved design, ready for implementation plan

## Problem

The "slides" (carousel) block currently assumes **every** slide carries an image:
the block's `slides` attachment collection is matched to `SlidesData::items[]` **by
position**. That breaks as soon as a slide is text-only — the positional mapping
becomes ambiguous and silently misaligns captions with images.

A slide is potentially:

- text (`title`, `caption`),
- a link (a single `CtaData`),
- **and, optionally, an attachment**.

Two capabilities are missing:

1. Associating an attachment to a **specific** slide (some slides have none).
2. Choosing the carousel's **display format** — `Square`, `Widescreen`,
   `BasisPortrait`, `BasisLandscape`.

This spec also folds in three coherence fixes for regressions/drift introduced in
the recent Blocks + Enums changes.

## Decisions

| Question | Decision |
| --- | --- |
| Format scope | **Block-level** — one format for the whole carousel (matches `cover_ratio` on Hero/Content/Features; a carousel of mixed ratios breaks alignment). |
| Format enum | **Rename `CoverRatio` → `MediaRatio`**, shared by cover blocks and slides. Values unchanged. Removes the "Cover" semantic mismatch on a block that has no cover. |
| Slide → media link | **`?string $attachment_uuid` on `SlideItemData`**, referencing an `Attachment` row's `uuid` in the block's `slides` collection. Reorder-safe, precise, `null` = text-only slide. |

The attachment `uuid` is stable: `AttachmentObserver` auto-generates it on create
(`src/Observers/AttachmentObserver.php:18-19`).

## Design

### 1. Enum `MediaRatio` (renamed from `CoverRatio`)

- Rename `src/Enums/CoverRatio.php` → `src/Enums/MediaRatio.php`, class
  `CoverRatio` → `MediaRatio`.
- Update every reference: `HeroData`, `ContentData`, `FeaturesData` (their
  `cover_ratio` prop stays named `cover_ratio`; only its **type** changes to
  `MediaRatio`).
- Fix the docblock: it currently is a copy-paste of `CoverLayout`'s doc ("as a
  full background, stacked before or after the content, or floated to one side").
  Rewrite it to describe an **aspect ratio** (square, widescreen, portrait,
  landscape; `Inherit` defers to the theme).
- Values unchanged: `Inherit`, `Square`, `Widescreen`, `BasisPortrait`,
  `BasisLandscape`.

### 2. `SlideItemData` — optional media link

```php
class SlideItemData extends Data
{
    public function __construct(
        #[Nullable, Max(255)]
        public ?string $title = null,
        #[Nullable]
        public ?string $caption = null,
        #[Nullable]
        public ?string $attachment_uuid = null,
        public ?CtaData $cta = null,
    ) {}
}
```

- `attachment_uuid === null` → text-only slide.
- Otherwise it references one entry of the block's `slides` collection by
  `Attachment::uuid`.
- Update the class docblock: the slide image is no longer "matched by position";
  it is resolved by `attachment_uuid` against the `slides` collection.

### 3. `SlidesData` — block-level format + dedup fix

```php
class SlidesData extends PropsData
{
    public function __construct(
        public bool $autoplay = false,
        #[Min(0)]
        public int $interval = 5000,
        public bool $loop = true,
        public MediaRatio $media_ratio = MediaRatio::Inherit,
        #[DataCollectionOf(SlideItemData::class)]
        public array $items = [],
        #[DataCollectionOf(CtaData::class)]
        public array $ctas = [],
        public SlidesStylesData $styles = new SlidesStylesData,
    ) {}
}
```

- **Add** `media_ratio` (a prop, placed like `cover_ratio` on the other blocks —
  not in styles).
- **Remove** `navPosition` from the props: it was duplicated into both
  `SlidesData` (props) and `SlidesStylesData` (styles) with the same default.
  Navigation position is presentation → it lives in styles only, consistent with
  `nav_size` which already lives only there.
- Update the class docblock accordingly (position matching → uuid resolution; add
  `media_ratio` to the "presentation vs behaviour" note).

### 4. Coherence fixes (recent Blocks + Enums drift)

Established convention for multi-word `Data` fields is **snake_case**
(`cover_layout`, `cover_ratio`, `form_key`, `per_view`). The recent changes
introduced camelCase fields; bring them back in line:

- `SlidesStylesData`: `navPosition` → `nav_position`, `navSize` → `nav_size`
  (and `navPosition` removed from `SlidesData` props as above).
- `BlockStyleData`: `marginX` → `margin_x`, `marginY` → `margin_y`,
  `spaceY` → `space_y`.

`CoverRatio` docblock fix is covered in §1 (the enum rename).

### 5. Validation

Resolved during planning: validation lives on the **Data attribute**, not in
`PublishBlockRequest`.

- `SlideItemData::$attachment_uuid` carries `#[Nullable, Max(36)]` — a well-formed,
  bounded, optional reference (a `Str::uuid()` is 36 chars). Nulls are allowed
  (text-only slides). This matches how every other prop validates: through the
  typed `PropsData` attributes, not the request.
- **Not** enforced in `PublishBlockRequest`: it validates the top-level shape and
  the `slides`/`cover` uploads only, and treats `props` opaquely
  (`['nullable','array']`). Adding slides-specific nested rules there would couple
  the generic request to one template, and it cannot check referential integrity
  at create time anyway — uploaded slides receive their `uuid` only after
  `AttachUploadedFiles::handle` runs, so no uuid exists to reference yet.
- **Referential integrity** (a non-null `attachment_uuid` points at a real
  attachment in this block's `slides` collection) is a host concern, resolved when
  persisting/rendering. Documented in the block docs and the `SlideItemData`
  docblock. The typical flow is upload-then-link: create the slides, read back
  their uuids, then set `items[].attachment_uuid`.

### 6. Front-end (host) contract

- Rendering resolves each slide's image by `items[].attachment_uuid` against the
  serialized `slides[]` (each `AttachmentData` carries `uuid`), instead of
  index-based pairing.
- A slide with `attachment_uuid === null` renders text-only.
- `media_ratio` applies one aspect ratio to the whole carousel.

## Impact & blast radius

- **Package only** — no host copy exists (`App\Publish` namespace absent).
- `CoverRatio` rename touches: the enum file + `HeroData`, `ContentData`,
  `FeaturesData`.
- Field renames touch: `SlidesStylesData`, `BlockStyleData`, `SlidesData`.
- Regenerate TypeScript: `oi:gen-ts` (new `MediaRatio`, renamed fields, new
  `attachment_uuid` / `media_ratio`).
- Update: `SlidesData`/`PublishBlock` factory states for slides, existing slides
  tests, and the slides block documentation.
- Run `composer sync-ai-skills` after behaviour changes.

## Out of scope

- Per-slide format override (block-level only, by decision).
- Any change to the `cover_ratio` **prop name** on Hero/Content/Features (only the
  enum type is renamed).
- Front-end/Inertia rendering implementation (host concern; this package ships the
  data layer only).
