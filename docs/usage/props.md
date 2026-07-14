---
title: Typed props
description: How the props JSON column is typed with spatie/laravel-data and the PropsCast.
order: 4
---

# Typed props

The `props` JSON column on pages and blocks is cast to a `PropsData` — a
`spatie/laravel-data` object. The concrete subclass is resolved from the row's
`template_key`:

- if the template declares a `propsClass`, that typed class is hydrated;
- otherwise the permissive `GenericPropsData` bag is used.

Because the cast's return type is `PropsData`, the column is discoverable by
`oi-lab/oi-laravel-ts`.

## Reading and writing

```php
$hero->props;                       // HeroData (template 'hero' declares HeroData)
$hero->props->pre;                  // typed access
$hero->props->styles->title->tag;   // HeadingTag::H2

$page->props;             // GenericPropsData (default template, no propsClass)
$page->props->value('foo', 'default');
```

`props` accepts either a raw array or a `PropsData` instance on write, and always
reads back as the resolved `PropsData`:

```php
use OiLab\OiLaravelPublish\Data\Blocks\ContentData;

$block->update(['props' => new ContentData(format: 'html')]);
$block->update(['props' => ['format' => 'html']]); // equivalent raw form
```

## Props never carry content

A block's title, lead and body are its `name`, `excerpt` and `description`
**columns**. Props hold what is specific to the template, plus two cross-cutting
keys every block shares: `ctas` and `styles`.

```php
$block->name;         // the rendered title
$block->excerpt;      // the lead paragraph
$block->description;  // the rich body, rendered with the configured renderer
$block->props->pre;   // template-specific: the kicker above the title
```

Duplicating a column in props would leave two candidate sources of truth for the
same string. Where a template genuinely needs a second heading, it names it
something else — `pre`, on `hero`, `warranty`, and each `features` item.

## Bundled typed props

`HeroData`, `FeaturesData` (+ `FeatureItemData`), `BlockquoteData`, `ContentData`,
`FormData`, `SlidesData` (+ `SlideItemData`), `BreadcrumbData`
(+ `BreadcrumbItemData`), `MapData`, `TableData`, `WarrantyData`
(+ `WarrantyItemData`), `FaqsData` (+ `FaqItemData`). See the
[block catalogue](./blocks.md#block-catalogue) for each one's props.

## Calls to action

Every block but `breadcrumb` and `faqs` carries a `ctas` collection of `CtaData`,
so a block can offer several actions rather than the single `cta_label` /
`cta_url` pair it used to:

```php
$hero->props->ctas[0]->label;     // 'Get started'
$hero->props->ctas[0]->variant;   // CtaVariant::Default
$hero->props->ctas[0]->position;  // CtaPosition::Header
```

| Field | Type | Meaning |
| ----- | ---- | ------- |
| `label` | string | required |
| `url` | string | required |
| `target` | `_self` `_blank` | |
| `variant` | `default` `secondary` `ghost` `link` | visual weight |
| `size` | `xs` `sm` `default` `lg` | size of this button |
| `position` | `header` `body` `footer` — or null | which slot it renders in |

`position` is nullable because a slide has exactly one CTA and no slot to place
it in: `SlideItemData::$cta` is a single `CtaData` that leaves it null.

`CtaSize` sizes one button. It is deliberately distinct from `TextScale` below,
which sets the typographic scale of a whole group — a `lg` primary can sit next
to an `xs` secondary inside a group whose scale is `base`.

## Styles

Every block carries a `styles` object describing its presentation. Each block has
its own `*StylesData` class exposing only the slots it can actually use: a hero
has no carousel, a breadcrumb has no calls to action.

```php
$hero->props->styles->block->theme;       // BlockTheme::Light
$hero->props->styles->title->tag;         // HeadingTag::H2
$features->props->styles->list->columns;  // BreakpointsData
```

| Slot | Class | Applies to |
| ---- | ----- | ---------- |
| `block` | `BlockStyleData` | height (`inherit`, `screen`), `items-*`, `justify-*`, theme |
| `title` | `HeadingStyleData` | heading tag `h1`–`h6`, horizontal alignment |
| `excerpt` / `description` | `TextStyleData` | horizontal alignment |
| `ctas` | `CtasStyleData` | typographic scale, horizontal alignment |
| `quote` | `QuoteStyleData` | typographic scale |
| `list` | `ListStyleData` | columns per breakpoint, ordered/unordered, marker |
| `carousel` | `CarouselStyleData` | visible slides per breakpoint |

`BreakpointsData` is a responsive integer: `base` always applies, each breakpoint
overrides it upwards, and a null one inherits from the breakpoint below. Its
`xxl` field maps to Tailwind's `2xl` — a PHP property cannot start with a digit.

Every field has a default, so an absent or partial `styles` hydrates cleanly:

```php
HeroData::from(['styles' => ['title' => ['tag' => 'h1']]])
    ->styles->title->align;  // HorizontalAlign::Left — the untouched sibling default
```

Style classes are **composed, never inherited**. `oi-laravel-ts` reads only the
constructor of the class it reflects, so a property inherited from a parent would
vanish from the generated TypeScript interface without any warning.

## Adapting the classes in your project

`php artisan publish:install-data` copies the block, style and enum classes into
your application (`app/Publish/` by default), rewriting their namespaces. The
copies keep extending the package's `PropsData`, so the cast still hydrates them.

```bash
php artisan publish:install-data --namespace="App\Publish"
```

Two steps remain afterwards: point each `templates.*.propsClass` at its new
counterpart, and in `config/oi-laravel-ts.php` replace
`OiLab\OiLaravelPublish\Data` with your namespace in `data_namespaces` — keeping
both would abort generation on a short-name collision.

## Adding a typed props class

Create a class extending `PropsData` and point a template's `propsClass` at it:

```php
namespace App\Publish;

use OiLab\OiLaravelPublish\Data\PropsData;
use Spatie\LaravelData\Attributes\Validation\Nullable;

class PricingData extends PropsData
{
    public function __construct(
        public string $plan,
        #[Nullable]
        public ?string $price = null,
    ) {}
}
```

Extending `PropsData` (rather than `Data` directly) lets the cast return a single
`PropsData` type while serialising your class's own fields.
