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
$hero->props;             // HeroData (template 'hero' declares HeroData)
$hero->props->heading;    // typed access

$page->props;             // GenericPropsData (default template, no propsClass)
$page->props->value('foo', 'default');
```

`props` accepts either a raw array or a `PropsData` instance on write, and always
reads back as the resolved `PropsData`:

```php
use OiLab\OiLaravelPublish\Data\Blocks\ContentData;

$block->update(['props' => new ContentData(body: 'Hello', format: 'markdown')]);
$block->update(['props' => ['body' => 'Hello']]); // equivalent raw form
```

## Bundled typed props

`HeroData`, `FeaturesData` (+ `FeatureItemData`), `BlockquoteData`, `ContentData`,
`FormData`, `SlidesData` (+ `SlideItemData`), `BreadcrumbData`
(+ `BreadcrumbItemData`), `MapData`, `TableData`.

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
