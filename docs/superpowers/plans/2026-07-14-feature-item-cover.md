# FeatureItem Cover + Ratio Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Give each feature item an optional per-item cover image (linked by attachment uuid from a new block-level `gallery` collection) at its own `MediaRatio`.

**Architecture:** Package-only change in `oi-lab/oi-laravel-publish` (data layer; no host copy exists). Add a `gallery` attachment collection to blocks (config + `PublishBlock::gallery()` + `PublishBlockData.gallery`, mirroring the existing `slides` collection). Add `attachment_uuid` and `cover_ratio` to `FeatureItemData`, reusing the exact name/mechanism shipped for `SlideItemData` and the shared `MediaRatio` enum. Add gallery upload rules to `PublishBlockRequest`. Referential integrity of the uuid is a host concern, deliberately not enforced in the request.

**Tech Stack:** PHP 8.4, Laravel 12, `spatie/laravel-data`, `oi-lab/oi-laravel-attachments`, Pest v4, Laravel Pint.

## Global Constraints

- PHP 8.4; explicit return types; curly braces on all control structures; constructor property promotion.
- `Data` multi-word fields are snake_case (`cover_ratio`, `attachment_uuid`, `per_view`).
- Props never carry content; a reference id (`attachment_uuid`) is a link, not content, and is allowed.
- Style/DTO classes are composed, never inherited (`oi-laravel-ts` reflects only the constructor).
- Resolve models/templates through `OiLab\OiLaravelPublish\OiLaravelPublish`; never hardcode `::class`.
- The `gallery` collection mirrors `slides` exactly: same relation shape (`$this->attachments('gallery')`), same `Optional` DTO treatment (present only when the relation is eager-loaded).
- Validation for `attachment_uuid` lives ONLY on the Data attribute (`#[Nullable, Max(36)]`), never in `PublishBlockRequest`. The request validates the top-level shape and uploads only, treating `props` opaquely.
- `attachment_uuid` name and `#[Nullable, Max(36)]` rule match `SlideItemData` verbatim. `cover_ratio` is typed `MediaRatio`, default `MediaRatio::Inherit`.
- Run `vendor/bin/pint --dirty` before each commit. Tests are Pest: `vendor/bin/pest` from the package root.
- Work from `packages/oi-lab/oi-laravel-publish/` — the git repo root for these commits.
- No migration: attachment collection names are data, not schema.
- After behaviour changes, `composer sync-ai-skills` regenerates the AI context. `oi:gen-ts` is host-side and out of package scope — do not run it.

---

### Task 1: `gallery` attachment collection (block-level)

Add a third block collection alongside `cover` and `slides`, wired end-to-end (config → model relation → DTO), so a feature item's cover can be sourced from it.

**Files:**
- Modify: `config/oi-laravel-publish.php:57`
- Modify: `src/Models/PublishBlock.php` (docblock `@property-read`, new `gallery()` after `slides()`)
- Modify: `src/Data/PublishBlockData.php` (constructor param, `@param`, `Optional` docblock note, `fromModel()`)
- Test: `tests/Feature/DataFromModelTest.php` (append two tests)

**Interfaces:**
- Consumes: nothing from earlier tasks.
- Produces: `PublishBlock::gallery(): MorphMany<Attachment, $this>`; `PublishBlockData::$gallery` (`array|Optional`, default `new Optional`), populated in `fromModel()` when the relation is loaded. Block attachment collections are now `['cover', 'slides', 'gallery']`.

- [ ] **Step 1: Write the failing tests**

Append to `tests/Feature/DataFromModelTest.php`, inside the existing `describe('cover and slides on PublishBlockData', ...)` block is NOT required — add these as top-level `it(...)` at the end of the file (all needed imports — `File`, `PublishBlockData`, `PublishBlock`, `PublishPage`, `Optional` — are already at the top):

```php
it('carries the ordered gallery collection once eager loaded', function () {
    $page = PublishPage::factory()->create();
    $block = PublishBlock::factory()->forPage($page)->hero()->create();
    $first = File::factory()->create();
    $second = File::factory()->create();
    $block->attachFile($first, 'gallery');
    $block->attachFile($second, 'gallery');

    $data = PublishBlockData::fromModel($block->fresh()->load('gallery.file'));

    expect($data->gallery)->toHaveCount(2)
        ->and($data->gallery[0]->file_id)->toBe($first->id)
        ->and($data->gallery[1]->file_id)->toBe($second->id)
        ->and($data->toArray()['gallery'])->toHaveCount(2);
});

it('omits gallery from the payload when the relation is not loaded', function () {
    $page = PublishPage::factory()->create();
    $block = PublishBlock::factory()->forPage($page)->hero()->create();

    $data = PublishBlockData::fromModel($block);

    expect($data->gallery)->toBeInstanceOf(Optional::class)
        ->and($data->toArray())->not->toHaveKey('gallery');
});
```

- [ ] **Step 2: Run tests to verify they fail**

Run: `vendor/bin/pest tests/Feature/DataFromModelTest.php --filter="gallery"`
Expected: FAIL — `PublishBlockData` has no `gallery` property, and `PublishBlock` has no `gallery` relation to eager-load.

- [ ] **Step 3: Register the collection in config**

In `config/oi-laravel-publish.php:57`, change:

```php
        'block' => ['cover', 'slides'],
```
to:
```php
        'block' => ['cover', 'slides', 'gallery'],
```

- [ ] **Step 4: Add the model relation**

In `src/Models/PublishBlock.php`, add the `@property-read` line to the class docblock right after the existing `slides` one:

```php
 * @property-read Collection<int, Attachment> $gallery
```

And add this method immediately after the `slides()` method:

```php
    /**
     * The ordered `gallery` attachment collection for this block.
     *
     * @return MorphMany<Attachment, $this>
     */
    public function gallery(): MorphMany
    {
        return $this->attachments('gallery');
    }
```

- [ ] **Step 5: Add the DTO field and mapping**

In `src/Data/PublishBlockData.php`:

Add to the `@param` block (after the `slides` line):

```php
     * @param  AttachmentData[]|Optional  $gallery
```

Add the constructor parameter immediately after `$slides`:

```php
        public array|Optional $gallery = new Optional,
```

In `fromModel()`, add after the `slides:` argument:

```php
            gallery: $block->relationLoaded('gallery')
                ? $block->gallery->map(fn (Attachment $g): AttachmentData => $g->toData())->all()
                : new Optional,
```

Update the class docblock's `Optional` note (the paragraph ending "…where a null `cover` means 'loaded, and there is no cover'.") to mention `gallery` alongside `slides`, e.g. change "`cover` and `slides` are `Optional`" to "`cover`, `slides` and `gallery` are `Optional`".

- [ ] **Step 6: Run tests + pint**

Run: `vendor/bin/pest tests/Feature/DataFromModelTest.php && vendor/bin/pint --dirty`
Expected: PASS (all DataFromModel tests green).

- [ ] **Step 7: Commit**

```bash
git add -A
git commit -m "feat: add block-level gallery attachment collection"
```

---

### Task 2: `FeatureItemData` — optional cover + ratio

Add the two per-item fields, reusing the `SlideItemData` pattern verbatim.

**Files:**
- Modify: `src/Data/Blocks/FeatureItemData.php`
- Test: `tests/Feature/PublishBlockTest.php` (append three tests; add imports)

**Interfaces:**
- Consumes: `MediaRatio` (shipped); the `gallery` collection (Task 1) is the host-side resolution target, not a code dependency here.
- Produces: `FeatureItemData` constructor `(string $title, ?string $text, ?string $icon, ?string $attachment_uuid, MediaRatio $cover_ratio, CtaData[] $ctas)`. `attachment_uuid` default `null` (`#[Nullable, Max(36)]`); `cover_ratio` default `MediaRatio::Inherit`.

- [ ] **Step 1: Write the failing tests**

At the top of `tests/Feature/PublishBlockTest.php`, add any of these imports that are missing (`FeaturesData` and `MediaRatio` and `PublishBlock` are already imported; verify `PublishPage` is too and add if absent):

```php
use Illuminate\Validation\ValidationException;
use OiLab\OiLaravelPublish\Data\Blocks\FeatureItemData;
```

Append these three tests at the end of the file:

```php
it('carries an optional cover and ratio on a feature item', function () {
    $item = FeatureItemData::from([
        'title' => 'Fast',
        'attachment_uuid' => '9b7c1e2a-4f56-4c3d-8a1b-2e3f4a5b6c7d',
        'cover_ratio' => 'widescreen',
    ]);

    expect($item->attachment_uuid)->toBe('9b7c1e2a-4f56-4c3d-8a1b-2e3f4a5b6c7d')
        ->and($item->cover_ratio)->toBe(MediaRatio::Widescreen)
        // Defaults: no cover, ratio inherits from the block.
        ->and(FeatureItemData::from(['title' => 'Plain'])->attachment_uuid)->toBeNull()
        ->and(FeatureItemData::from(['title' => 'Plain'])->cover_ratio)->toBe(MediaRatio::Inherit);
});

it('rejects a feature item attachment_uuid longer than 36 chars', function () {
    FeatureItemData::validate(['title' => 'X', 'attachment_uuid' => str_repeat('a', 37)]);
})->throws(ValidationException::class);

it('round-trips feature item covers through the json props column', function () {
    $page = PublishPage::factory()->create();
    $block = PublishBlock::factory()->forPage($page)->template('features')->create([
        'props' => ['items' => [
            ['title' => 'Fast', 'attachment_uuid' => '9b7c1e2a-4f56-4c3d-8a1b-2e3f4a5b6c7d', 'cover_ratio' => 'square'],
            ['title' => 'Simple'],
        ]],
    ]);

    $block->refresh();

    expect($block->props)->toBeInstanceOf(FeaturesData::class)
        ->and($block->props->items[0]->attachment_uuid)->toBe('9b7c1e2a-4f56-4c3d-8a1b-2e3f4a5b6c7d')
        ->and($block->props->items[0]->cover_ratio)->toBe(MediaRatio::Square)
        ->and($block->props->items[1]->attachment_uuid)->toBeNull();
});
```

- [ ] **Step 2: Run tests to verify they fail**

Run: `vendor/bin/pest tests/Feature/PublishBlockTest.php --filter="feature item"`
Expected: FAIL — `FeatureItemData` has no `attachment_uuid` / `cover_ratio`, so the values are dropped and the over-length input is not rejected.

- [ ] **Step 3: Add the fields**

Replace the contents of `src/Data/Blocks/FeatureItemData.php` with:

```php
<?php

namespace OiLab\OiLaravelPublish\Data\Blocks;

use OiLab\OiLaravelPublish\Data\CtaData;
use OiLab\OiLaravelPublish\Enums\MediaRatio;
use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Attributes\Validation\Max;
use Spatie\LaravelData\Attributes\Validation\Nullable;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Data;

/**
 * A single item inside a "features" block.
 *
 * An item is potentially text (`title`, `text`), an `icon`, an optional cover
 * image, and calls to action. `attachment_uuid` links the item to one entry of
 * the block's `gallery` attachment collection by that attachment's stable
 * `uuid`; it is null for an item with no cover. The host resolves the image by
 * uuid (not by position) and enforces that the uuid belongs to the block.
 * `cover_ratio` is the item's aspect ratio; `MediaRatio::Inherit` defers to the
 * block's `cover_ratio`, then the theme.
 */
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

- [ ] **Step 4: Run tests + pint**

Run: `vendor/bin/pest tests/Feature/PublishBlockTest.php && vendor/bin/pint --dirty`
Expected: PASS.

- [ ] **Step 5: Commit**

```bash
git add -A
git commit -m "feat: optional per-item cover (attachment_uuid) and cover_ratio on FeatureItemData"
```

---

### Task 3: `PublishBlockRequest` — gallery upload rules

Accept `gallery` uploads the same way the request accepts `slides`.

**Files:**
- Modify: `src/Http/Requests/PublishBlockRequest.php` (add two rule keys after `slides.*`)
- Test: `tests/Feature/RequestValidationTest.php` (append one test)

**Interfaces:**
- Consumes: nothing from earlier tasks.
- Produces: `PublishBlockRequest::rules()` includes `'gallery' => ['nullable','array','max:{maxFiles}']` and `'gallery.*' => ['file','image','max:{maxSize}']`.

- [ ] **Step 1: Write the failing test**

Append to `tests/Feature/RequestValidationTest.php` (the `PublishBlockRequest` import is already at the top):

```php
it('exposes gallery upload rules on the block request', function () {
    $rules = (new PublishBlockRequest)->rules();

    expect($rules)->toHaveKey('gallery')
        ->and($rules['gallery'])->toContain('nullable')
        ->and($rules['gallery'])->toContain('array')
        ->and($rules)->toHaveKey('gallery.*')
        ->and($rules['gallery.*'])->toContain('file')
        ->and($rules['gallery.*'])->toContain('image');
});
```

- [ ] **Step 2: Run test to verify it fails**

Run: `vendor/bin/pest tests/Feature/RequestValidationTest.php --filter="gallery upload rules"`
Expected: FAIL — `rules()` has no `gallery` / `gallery.*` keys.

- [ ] **Step 3: Add the rules**

In `src/Http/Requests/PublishBlockRequest.php`, add these two entries immediately after the `'slides.*' => [...]` line (reusing the existing `$maxFiles` / `$maxSize` locals):

```php
            'gallery' => ['nullable', 'array', "max:{$maxFiles}"],
            'gallery.*' => ['file', 'image', "max:{$maxSize}"],
```

- [ ] **Step 4: Run tests + pint**

Run: `vendor/bin/pest tests/Feature/RequestValidationTest.php && vendor/bin/pint --dirty`
Expected: PASS.

- [ ] **Step 5: Commit**

```bash
git add -A
git commit -m "feat: accept gallery uploads on PublishBlockRequest"
```

---

### Task 4: Docs, AI context, full suite

Document the new collection and per-item cover, regenerate the AI context, and run the whole suite green.

**Files:**
- Modify: `docs/usage/blocks.md` (Features section, lines 89-101)
- Modify: `docs/usage/attachments.md` (Blocks bullet, lines 13-14)

**Interfaces:**
- Consumes: everything from Tasks 1-3.
- Produces: docs describing per-item covers and the `gallery` collection; regenerated AI context; green suite.

- [ ] **Step 1: Update the features block doc**

In `docs/usage/blocks.md`, replace the Features section (from `### Features — \`features\`` through the `- **Default**:` line) with:

```markdown
### Features — `features`

A grid of feature items (`FeatureItemData`).

| Prop | Type | Notes |
|------|------|-------|
| `items` | `FeatureItemData[]` | `title` (req, ≤255), `text?`, `icon?` (≤255), `attachment_uuid?`, `cover_ratio`, `ctas` |
| `cover_layout` | `CoverLayout` | how the block-level cover is arranged (default `right`) |
| `cover_ratio` | `MediaRatio` | block-level cover aspect ratio (default `inherit`) |

- **Media**: an optional block-level `cover` (arranged by `cover_layout`), plus a `gallery` collection — each item links one entry by `attachment_uuid` and shows it at its own `cover_ratio` (`inherit` defers to the block's).
- **Styles**: `block`, `title`, `excerpt`, `ctas`, `list`.
- **Default**: `styles.list.columns` = `{ base: 1, md: 3 }` (seeded in config).
```

- [ ] **Step 2: Update the attachments doc**

In `docs/usage/attachments.md`, replace the Blocks bullet (the `- **Blocks**: ...` item that currently mentions `cover` and `slides`) with:

```markdown
- **Blocks**: `cover` (single image), `slides` (ordered gallery for the `slides`
  carousel; each slide links one entry by `attachment_uuid`), and `gallery`
  (ordered images for feature-item covers; each item links one entry by
  `attachment_uuid`).
```

- [ ] **Step 3: Regenerate the AI context**

Run: `composer sync-ai-skills`
Expected: regenerates the bundled AI skill without error. Review the diff (it may be empty if the skill docs don't enumerate per-collection details — that is fine).

- [ ] **Step 4: Run the full suite + pint**

Run: `vendor/bin/pest && vendor/bin/pint`
Expected: entire suite PASS; pint clean.

- [ ] **Step 5: Commit**

```bash
git add -A
git commit -m "docs: document gallery collection and feature-item covers"
```

---

## Post-plan (host application, out of package scope)

- Run `oi:gen-ts` in the host to regenerate `interfaces.ts`: `IFeatureItemData` gains `attachment_uuid` / `cover_ratio`; `IPublishBlockData` gains `gallery`.
- Wire the front-end features grid to resolve each item's image by `items[].attachment_uuid` against the serialized `gallery[]`, render text/icon-only when null, and apply `cover_ratio` (falling back to the block's `cover_ratio` when `Inherit`).
- Enforce, when persisting, that a non-null item `attachment_uuid` belongs to the block's `gallery` collection (upload-then-link flow).

## Notes on approach

- `gallery` mirrors `slides` end-to-end so the two multi-attachment collections stay symmetrical (relation, DTO `Optional` treatment, request rules).
- `FeatureItemData.attachment_uuid` reuses the `SlideItemData` field name and `#[Nullable, Max(36)]` rule verbatim; `cover_ratio` reuses the shared `MediaRatio` enum and the block-level ratio field name — no new concepts.
- No `PublishBlockRequest` nested-props validation and no migration, per the spec.
