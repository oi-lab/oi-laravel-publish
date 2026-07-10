---
title: Blocks
description: Attach ordered PublishBlock content to a page using block templates.
order: 2
---

# Blocks

A `PublishBlock` belongs to a single page (`publish_page_id`) and renders one
piece of content — a hero, a feature grid, a quote, a carousel, and so on. Blocks
are a **flat, ordered list** per page (ordered by `sort`); they are not recursive.

## Creating blocks

```php
use OiLab\OiLaravelPublish\Models\PublishBlock;

$hero = PublishBlock::create([
    'publish_page_id' => $page->id,
    'template_key'    => 'hero',
    'name'            => 'Welcome',      // the rendered title
    'key'             => 'hero',
    'excerpt'         => 'A short lead.',
    'props'           => ['pre' => 'New', 'styles' => ['title' => ['align' => 'center']]],
    'sort'            => 0,
]);
```

`props` resolves to the typed class declared by the block's template — here a
`HeroData`. See [Typed props](./props.md).

## Keys and ordering

`key` is a per-page identifier (e.g. an in-page anchor) and is **unique per
page** — the same key may be reused on another page. Blocks read back ordered by
`sort`:

```php
$page->blocks;            // ordered by `sort`
$block->page;             // parent PublishPage
$block->template();       // PublishTemplateData
```

## Fields

| Field | Type | Notes |
|-------|------|-------|
| `name` | string | required — the block's **rendered title** |
| `key` | string | unique per `publish_page_id` |
| `excerpt` | string? | the lead paragraph |
| `description` | text? | the body, rendered with the block renderer |
| `props` | json | template-specific fields, plus `ctas` and `styles` — never content. Typed via [PropsCast](./props.md) |
| `template_key` | string | references a block [template](./templates.md) |
| `publish_page_id` | int | owning page |
| `sort` | int | ordering within the page |
| `is_active` | bool | defaults to `true` |

Deleting a page cascades to its blocks. Blocks are soft-deletable and carry the
same audit/uuid columns as pages.
