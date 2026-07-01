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
