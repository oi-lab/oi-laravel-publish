---
title: Pages
description: Create recursive PublishPage models with parent/children, slugs and ordering.
order: 1
---

# Pages

A `PublishPage` is a recursive content page. Pages form a tree through
`parent_id`, expose `children` ordered by `sort`, reference a page template by
`template_key`, and carry typed `props`.

## Creating pages

```php
use OiLab\OiLaravelPublish\Models\PublishPage;

$home = PublishPage::create([
    'template_key' => 'landing',
    'name'         => 'Home',
    'slug'         => 'home',
]);

$about = PublishPage::create([
    'parent_id'    => $home->id,
    'template_key' => 'default',
    'name'         => 'About',
    'slug'         => 'about',
]);
```

## The page tree

```php
$home->children;   // Collection<PublishPage>, ordered by `sort`
$about->parent;    // PublishPage
$home->blocks;     // Collection<PublishBlock>, ordered by `sort`
```

## Slugs

Slugs are **unique per parent**, not globally — two pages under different parents
may share a slug, which suits path-based routing. A duplicate slug under the same
parent throws a `QueryException`.

## Params

A page's props are typed by `PagePropsData`, whose only field is `params`: an
ordered list of free-form `key` / `value` pairs. It is the escape hatch a page
needs for what the project alone knows about — a tracking id, a template
variant, an external reference — without a migration or a new typed field.

```php
$home->update(['props' => ['params' => [
    ['key' => 'gtm_id',  'value' => 'GTM-1234'],
    ['key' => 'variant', 'value' => 'b'],
]]]);

$home->param('gtm_id');              // 'GTM-1234'
$home->param('missing', 'fallback'); // 'fallback'
$home->params();                     // ['gtm_id' => 'GTM-1234', 'variant' => 'b']

$home->props->param('variant');      // same, straight off the typed props
$home->props->hasParam('variant');   // true
$home->props->params[0]->key;        // 'gtm_id' — the hydrated ParamData
```

Values are nullable strings: params are edited as text in the console, and the
host casts when it reads one. A missing param and a param holding null both read
as the default — `hasParam()` tells them apart. On a duplicate key, the last
occurrence wins.

`PublishPage::param()` / `params()` also read the raw `params` list of a page
whose template declares no `propsClass`, so a host page template left on the
generic bag keeps working.

## Fields

| Field | Type | Notes |
|-------|------|-------|
| `name` | string | required |
| `slug` | string | unique per `parent_id` |
| `excerpt` | string? | short summary |
| `description` | text? | rendered with the page renderer |
| `props` | json | typed via [PropsCast](./props.md) |
| `template_key` | string | references a page [template](./templates.md) |
| `parent_id` | int? | self-reference (recursive) |
| `sort` | int | sibling ordering |
| `is_active` | bool | defaults to `true` |

Pages are soft-deletable, carry `created_by` / `updated_by` audit columns, and a
generated `uuid`.
