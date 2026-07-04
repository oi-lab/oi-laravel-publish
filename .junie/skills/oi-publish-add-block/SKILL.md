---
name: oi-publish-add-block
description: >-
  Scaffold a new typed content block for oi-lab/oi-laravel-publish. Qualifies the
  block's nature and needs through a short questionnaire, then creates its
  PropsData class (and any nested item Data), registers the block template in
  config, wires it into page templates' allowedBlocks, sets up attachments,
  factory states and tests, and re-syncs AI context. Use whenever adding,
  creating, or scaffolding a new publish block, block template, or typed block
  props in a project (host app or the package itself) using oi-laravel-publish.
---

# Add a Publish Block

Create a new typed block for `oi-lab/oi-laravel-publish` **alongside the existing
ones** (`hero`, `features`, `content`, `blockquote`, `slides`, `form`,
`breadcrumb`, `map`, `table`). A block is three coordinated pieces:

1. A typed **`PropsData` subclass** in `src/Data/Blocks/<Name>Data.php` that
   declares and validates the block's props (spatie/laravel-data).
2. A **template entry** in `config/oi-laravel-publish.php` that binds a string
   `key` to that `propsClass`, its default `props`, and a human `name`.
3. Its **placement** — the `key` added to the `allowedBlocks` of every page
   template that may contain it.

Read `SKILL.md` of `oilab-laravel-publish` for the package's core concepts before
starting. Never hand-edit generated TypeScript or the synced skill copies.

## 1. Qualify the block (ask the user)

Before writing any code, gather the information that defines the block. Ask the
user for anything missing — do not invent props. Use `AskUserQuestion` (or a
plain numbered list) to collect:

- **key** — unique lowercase slug referenced by `template_key` (e.g. `pricing`,
  `cta`, `gallery`). Must not collide with an existing block key.
- **name** — human label shown in the editor (e.g. `Pricing table`).
- **description** — one sentence explaining what the block renders.
- **props** — for **each** field: name, PHP type (`string`, `?string`, `int`,
  `bool`, `float`, `array`), required or nullable, default value, and validation
  (e.g. `Max(255)`, `Min(0)`, `In([...])`, `Url`, `Email`).
- **repeatable items?** — does the block hold a list of sub-items (like
  `features` or `slides`)? If yes, collect the fields of a single item; this
  becomes a nested `<Name>ItemData` collection.
- **media** — does it need attachments? Blocks already expose `cover` and
  `slides` collections. Only add a new named collection if the block needs media
  beyond those two.
- **placement** — which page templates may contain it (`default`, `landing`, or
  a new page template). Defaults to `default`.
- **default props** — sensible values applied to every new block instance.

If the user's description is vague, propose a concrete prop list based on the
nature of the block and confirm it before generating.

## 2. Create the `PropsData` class

Create `src/Data/Blocks/<Name>Data.php`. It **must extend `PropsData`**, use
constructor property promotion, explicit types, and spatie validation
attributes. Mirror the style of the existing block data classes.

```php
<?php

namespace OiLab\OiLaravelPublish\Data\Blocks;

use OiLab\OiLaravelPublish\Data\PropsData;
use Spatie\LaravelData\Attributes\Validation\In;
use Spatie\LaravelData\Attributes\Validation\Max;
use Spatie\LaravelData\Attributes\Validation\Nullable;
use Spatie\LaravelData\Attributes\Validation\Required;

/**
 * Props for a "<key>" block: <one-line description>.
 */
class PricingData extends PropsData
{
    public function __construct(
        #[Nullable, Max(255)]
        public ?string $heading = null,
        #[Required, In(['monthly', 'yearly'])]
        public string $period = 'monthly',
        #[Min(0)]
        public int $columns = 3,
    ) {}
}
```

Rules:

- Extend `PropsData` (never `Data` directly) so `PropsCast` can hydrate it and
  `oi-laravel-ts` can emit its interface.
- Every promoted property is typed; nullable props default to `null`.
- Attach validation attributes; import each attribute class explicitly.
- Media (images) is **not** a prop — it lives in the `cover`/`slides`
  attachment collections. Do not add a `string $image` prop for uploads.

## 3. (If repeatable) create the item Data class

For a block that holds a list of sub-items, add `src/Data/Blocks/<Name>ItemData.php`.
The item class extends **`Data`** (not `PropsData` — items are not top-level
props), and the parent props collect them with `#[DataCollectionOf(...)]`.

```php
<?php

namespace OiLab\OiLaravelPublish\Data\Blocks;

use Spatie\LaravelData\Attributes\Validation\Max;
use Spatie\LaravelData\Attributes\Validation\Nullable;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Data;

/**
 * A single tier inside a "pricing" block.
 */
class PricingTierData extends Data
{
    public function __construct(
        #[Required, Max(255)]
        public string $title,
        #[Required, Max(255)]
        public string $price,
        #[Nullable]
        public ?string $description = null,
    ) {}
}
```

Then reference it from the block props:

```php
use Spatie\LaravelData\Attributes\DataCollectionOf;

/** @param array<int, PricingTierData> $tiers */
public function __construct(
    // ...other props
    #[DataCollectionOf(PricingTierData::class)]
    public array $tiers = [],
) {}
```

## 4. Register the block template in config

Edit `config/oi-laravel-publish.php`:

1. Add the `use` import for the new data class at the top with the others:
   `use OiLab\OiLaravelPublish\Data\Blocks\PricingData;`
2. Append a **block** entry to the `templates` array, under the
   `--- Block templates ---` comment:

```php
[
    'key' => 'pricing',
    'name' => 'Pricing table',
    'type' => PublishTemplateType::Block->value,
    'description' => 'A grid of pricing tiers.',
    'propsClass' => PricingData::class,
    'props' => ['period' => 'monthly', 'columns' => 3],
],
```

- `key` is the string used as `template_key`; it must be unique.
- `propsClass` binds the typed props via `PropsCast`.
- `props` holds the defaults applied to new blocks (match the data class
  defaults where sensible).

## 5. Add the key to page templates' `allowedBlocks`

In the same config, add the block `key` to the `allowedBlocks` array of every
page template that may contain it (at minimum `default`, unless the user said
otherwise):

```php
'allowedBlocks' => [
    'hero', 'features', 'content', 'blockquote', 'slides',
    'form', 'breadcrumb', 'map', 'table', 'pricing',
],
```

A block that is not listed in any page template's `allowedBlocks` cannot be
placed by the editor, so this step is required for the block to be usable.

## 6. (If needed) attachments

Blocks already own `cover` and `slides` collections (config `attachments.block`).
Only if the block needs an **additional** named media collection, add it there:

```php
'attachments' => [
    'page' => ['cover'],
    'block' => ['cover', 'slides', 'logos'],
    // ...
],
```

Otherwise skip this step.

## 7. Factory state + tests

Add a factory state for the block in `database/factories/PublishBlockFactory.php`
so tests can build it, mirroring the existing `hero()` state:

```php
public function pricing(): static
{
    return $this->state(fn (array $attributes): array => [
        'template_key' => 'pricing',
        'props' => ['period' => 'monthly', 'columns' => 3],
    ]);
}
```

Then add a Pest test (feature test in `tests/Feature/`, or extend
`PublishBlockTest.php`) covering the happy path and prop typing. Follow the
existing conventions:

```php
it('creates a pricing block with typed props', function () {
    $page = PublishPage::factory()->create();
    $block = PublishBlock::factory()->forPage($page)->pricing()->create(['key' => 'pricing']);

    expect($block->template_key)->toBe('pricing')
        ->and($block->props)->toBeInstanceOf(PricingData::class)
        ->and($block->props->period)->toBe('monthly');
});
```

Also assert the template is registered (extend the
`PublishTemplateRegistryTest` block-keys assertion to include the new key) and,
if the block holds repeatable items, cover the collection hydration.

## 8. Finalize

Run, in order, from the package root (or the host app root when the block is
added there):

```bash
vendor/bin/pint --dirty                 # format changed files
php artisan test --filter=Pricing       # or the relevant filter
composer sync-ai-skills                 # re-sync .claude / .junie skill copies
```

If the host app uses `oi-lab/oi-laravel-ts`, regenerate interfaces so the new
block's props reach the frontend union:

```bash
php artisan oi:gen-ts
```

Finally, add the new typed block to the block list in
`resources/stubs/ai-skill.md` (the `Data/Blocks/*` enumeration and the
`oi-laravel-ts` interfaces note) so the AI context stays accurate, then run
`composer sync-ai-skills` again.

## Checklist

- [ ] `src/Data/Blocks/<Name>Data.php` extends `PropsData`, typed + validated
- [ ] (if repeatable) `<Name>ItemData.php` extends `Data`, wired via `#[DataCollectionOf]`
- [ ] Template entry added to `config/oi-laravel-publish.php` with `propsClass` + defaults
- [ ] `key` added to relevant page templates' `allowedBlocks`
- [ ] (if needed) new attachment collection registered
- [ ] Factory state + Pest test covering the block
- [ ] `pint` clean, tests green
- [ ] `composer sync-ai-skills` run; `ai-skill.md` block list updated
- [ ] (host app) `php artisan oi:gen-ts` regenerated
