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
ones** (`hero`, `features`, `warranty`, `content`, `blockquote`, `slides`, `form`,
`breadcrumb`, `map`, `table`, `faqs`). A block is four coordinated pieces:

1. A typed **`PropsData` subclass** in `src/Data/Blocks/<Name>Data.php` that
   declares and validates the block's props (spatie/laravel-data).
2. Its **`*StylesData`** in `src/Data/Styles/<Name>StylesData.php`, describing
   how the block and its elements are presented.
3. A **template entry** in `config/oi-laravel-publish.php` that binds a string
   `key` to that `propsClass`, its default `props`, and a human `name`.
4. Its **placement** — the `key` added to the `allowedBlocks` of every page
   template that may contain it.

The block's **content** is not part of this: its title, lead and body are the
`name`, `excerpt` and `description` columns of `publish_blocks`.

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
  (e.g. `Max(255)`, `Min(0)`, `In([...])`, `Url`, `Email`). Remember that the
  block's title, lead and body are **columns** (`name`, `excerpt`,
  `description`), never props.
- **repeatable items?** — does the block hold a list of sub-items (like
  `features` or `slides`)? If yes, collect the fields of a single item; this
  becomes a nested `<Name>ItemData` collection.
- **styles** — which slots the block can style: `block` (height, alignment,
  theme), `title`, `excerpt`, `description`, `ctas`, `quote`, `list`,
  `carousel`. Every block gets at least `block`.
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

use OiLab\OiLaravelPublish\Data\CtaData;
use OiLab\OiLaravelPublish\Data\PropsData;
use OiLab\OiLaravelPublish\Data\Styles\PricingStylesData;
use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Attributes\Validation\In;
use Spatie\LaravelData\Attributes\Validation\Required;

/**
 * Props for a "<key>" block: <one-line description>.
 *
 * The title, lead and body come from the block's `name`, `excerpt` and
 * `description` columns.
 */
class PricingData extends PropsData
{
    /**
     * @param  CtaData[]  $ctas
     */
    public function __construct(
        #[Required, In(['monthly', 'yearly'])]
        public string $period = 'monthly',
        #[DataCollectionOf(CtaData::class)]
        public array $ctas = [],
        public PricingStylesData $styles = new PricingStylesData,
    ) {}
}
```

Rules:

- Extend `PropsData` (never `Data` directly) so `PropsCast` can hydrate it and
  `oi-laravel-ts` can emit its interface.
- **Props never carry content.** The block's title, lead and body are its `name`,
  `excerpt` and `description` **columns**. Never declare a `title`, `excerpt`,
  `description`, `heading`, `subheading`, `intro`, `overview`, `body` or
  `caption` prop — that duplication is exactly what this rule exists to stop.
  Props hold what is specific to the template (a `period`, a `form_key`, a
  `pre` kicker), plus the two cross-cutting keys below.
- **Always add `ctas` and `styles`**, unless the block is pure navigation.
  `ctas` is `array` + `#[DataCollectionOf(CtaData::class)]` + a `@param CtaData[]`
  annotation — that annotation is what makes oi-laravel-ts emit `ctas: ICtaData[]`.
- **Give the block its own `*StylesData`**, composing only the slots it can use
  (`block`, `title`, `excerpt`, `description`, `ctas`, `quote`, `list`,
  `carousel`). Compose, never inherit: oi-laravel-ts reads only the constructor of
  the class it reflects, so an inherited property vanishes from the generated
  interface without warning.
- Repeated collections are always named `items`, and their items use
  `title` / `text` / `icon` / `url`. Reach for a new name only when the block's
  domain owns the word (a quote's `quote`, a table's `headers` / `rows`).
- Every promoted property is typed; nullable props default to `null`; every style
  slot defaults to `new XStyleData`.
- Attach validation attributes; import each attribute class explicitly.
- Media (images) is **not** a prop — it lives in the `cover`/`slides`
  attachment collections. Do not add a `string $image` prop for uploads.

## 3. Create the block's styles class

Create `src/Data/Styles/<Name>StylesData.php`, composing one slot per stylable
element. Reuse the existing primitives — `BlockStyleData`, `HeadingStyleData`,
`TextStyleData`, `CtasStyleData`, `QuoteStyleData`, `ListStyleData`,
`CarouselStyleData` — and only write a new primitive when no existing one fits.

```php
<?php

namespace OiLab\OiLaravelPublish\Data\Styles;

use Spatie\LaravelData\Data;

/**
 * Presentation of a "pricing" block.
 */
class PricingStylesData extends Data
{
    public function __construct(
        public BlockStyleData $block = new BlockStyleData,
        public HeadingStyleData $title = new HeadingStyleData,
        public CtasStyleData $ctas = new CtasStyleData,
        public ListStyleData $list = new ListStyleData,
    ) {}
}
```

Every slot defaults to a fresh instance, and every primitive field has its own
default, so `['styles' => []]` — or a `styles` holding a single key — hydrates
without error and leaves the untouched siblings at their defaults.

Responsive integers (columns, slides per view) are a `BreakpointsData`:
`base` always applies, and `sm` / `md` / `lg` / `xl` / `xxl` override it upwards.
`xxl` maps to Tailwind's `2xl` — a PHP property cannot start with a digit.

## 4. (If repeatable) create the item Data class

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
        #[Nullable]
        public ?string $text = null,
        #[Required, Max(255)]
        public string $price,
    ) {}
}
```

An item's own fields follow the shared vocabulary (`title`, `text`, `icon`,
`url`); `price` is the one the pricing domain owns.

Then reference it from the block props — the collection is named `items`:

```php
use Spatie\LaravelData\Attributes\DataCollectionOf;

/** @param PricingTierData[] $items */
public function __construct(
    // ...other props
    #[DataCollectionOf(PricingTierData::class)]
    public array $items = [],
) {}
```

## 5. Register the block template in config

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
    'props' => ['styles' => ['list' => ['columns' => ['base' => 1, 'md' => 3]]]],
],
```

- `key` is the string used as `template_key`; it must be unique.
- `propsClass` binds the typed props via `PropsCast`.
- `props` holds only what **differs** from the data class defaults. Repeating a
  default here is noise that drifts the day the default changes.

## 6. Add the key to page templates' `allowedBlocks`

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

## 7. (If needed) attachments

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

## 8. Factory state + tests

Add a factory state for the block in `database/factories/PublishBlockFactory.php`
so tests can build it, mirroring the existing `hero()` state. The state fills the
**content columns** and only the props that differ from the defaults:

```php
public function pricing(): static
{
    return $this->state(fn (array $attributes): array => [
        'template_key' => 'pricing',
        'name' => fake()->sentence(3),
        'excerpt' => fake()->sentence(),
        'props' => [
            'period' => 'yearly',
            'ctas' => [
                ['label' => 'Start free', 'url' => '/signup', 'position' => 'footer'],
            ],
        ],
    ]);
}
```

Then add a Pest test (feature test in `tests/Feature/`, or extend
`PublishBlockTest.php`) covering the happy path, prop typing, and the style
defaults. Follow the existing conventions:

```php
it('creates a pricing block with typed props', function () {
    $page = PublishPage::factory()->create();
    $block = PublishBlock::factory()->forPage($page)->pricing()->create(['key' => 'pricing']);

    expect($block->template_key)->toBe('pricing')
        ->and($block->props)->toBeInstanceOf(PricingData::class)
        ->and($block->props->period)->toBe('yearly')
        ->and($block->props->ctas[0])->toBeInstanceOf(CtaData::class)
        ->and($block->props->ctas[0]->position)->toBe(CtaPosition::Footer)
        ->and($block->props->styles->title->tag)->toBe(HeadingTag::H2);
});
```

Also assert the template is registered (extend the
`PublishTemplateRegistryTest` block-keys assertion to include the new key) and,
if the block holds repeatable items, cover the collection hydration.

## 9. Finalize

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
- [ ] No `title` / `excerpt` / `description` prop — those are columns
- [ ] `ctas` (`CtaData[]`) and `styles` declared, unless the block is navigation
- [ ] `src/Data/Styles/<Name>StylesData.php` composed of flat primitives, never inherited
- [ ] (if repeatable) `<Name>ItemData.php` extends `Data`, wired via `#[DataCollectionOf]`
- [ ] Template entry added to `config/oi-laravel-publish.php` with `propsClass` + defaults
- [ ] `key` added to relevant page templates' `allowedBlocks`
- [ ] (if needed) new attachment collection registered
- [ ] Factory state + Pest test covering the block
- [ ] `pint` clean, tests green
- [ ] `composer sync-ai-skills` run; `ai-skill.md` block list updated
- [ ] (host app) `php artisan oi:gen-ts` regenerated
