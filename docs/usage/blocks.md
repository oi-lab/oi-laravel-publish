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

## Block catalogue

The package ships eleven block templates. Every block draws its **title, lead and
body** from the `name`, `excerpt` and `description` columns — the props below only
hold what is specific to the template, plus the two cross-cutting keys `ctas` and
`styles` ([details](./props.md)). Every block carries a `ctas` collection **except
`breadcrumb` and `faqs`**, which have no calls to action.

Each block's `styles` object exposes only the slots it can use; see the
[styles reference](./props.md#styles) for what each slot controls.

Blocks that render a `cover` attachment (`hero`, `features`, `content`) carry a
`cover_layout` prop — a `CoverLayout` enum choosing how the image is arranged
relative to the content: `background`, `before`, `after`, `left`, `right`
(default `right`).

### Hero — `hero`

Full-width headline with an optional kicker and a cover image.

| Prop | Type | Notes |
|------|------|-------|
| `pre` | string? | kicker shown above the title (≤255) |
| `cover_layout` | `CoverLayout` | how the cover is arranged (default `right`) |

- **Media**: `cover` attachment (arranged by `cover_layout`).
- **Styles**: `block`, `title`, `excerpt`, `ctas`.

### Features — `features`

A grid of feature items (`FeatureItemData`).

| Prop | Type | Notes |
|------|------|-------|
| `items` | `FeatureItemData[]` | `title` (req, ≤255), `text?`, `icon?` (≤255), `ctas` |
| `cover_layout` | `CoverLayout` | how an optional cover is arranged (default `right`) |

- **Media**: optional `cover` attachment (arranged by `cover_layout`).
- **Styles**: `block`, `title`, `excerpt`, `ctas`, `list`.
- **Default**: `styles.list.columns` = `{ base: 1, md: 3 }` (seeded in config).

### Blockquote — `blockquote`

A highlighted quotation; the quoted text is the `description` column.

| Prop | Type | Notes |
|------|------|-------|
| `author` | string? | attribution name (≤255) |
| `role` | string? | attribution role (≤255) |
| `source_url` | string? | link to the source (≤2048) |

- **Styles**: `block`, `quote`, `ctas`.

### Content — `content`

Free-form rich text; the body is the `description` column.

| Prop | Type | Notes |
|------|------|-------|
| `pre` | string? | kicker shown above the title (≤255) |
| `format` | `markdown` \| `html` | which renderer the host applies (default `markdown`) |
| `cover_layout` | `CoverLayout` | how an optional cover is arranged (default `right`) |

- **Media**: optional `cover` attachment (arranged by `cover_layout`).
- **Styles**: `block`, `title`, `description`, `ctas`.

### Form — `form`

Embeds a host-application form referenced by key.

| Prop | Type | Notes |
|------|------|-------|
| `form_key` | string | **required** — identifies the host form (≤255) |
| `action` | string? | override submit URL (≤2048) |
| `method` | `get` \| `post` | default `post` |
| `submit_label` | string? | the form's own submit control, not a CTA (≤255) |
| `success_message` | string? | shown after a successful submit |

- **Styles**: `block`, `title`, `ctas`.

### Slides — `slides`

A carousel; slide images live in the `slides` attachment collection, matched to
`items` by position.

| Prop | Type | Notes |
|------|------|-------|
| `autoplay` | bool | default `false` |
| `interval` | int | ms between slides (default `5000`, ≥0) |
| `loop` | bool | default `true` |
| `items` | `SlideItemData[]` | per-slide `title?`, `caption?`, and a single `cta?` |

- **Media**: `slides` attachment collection (ordered images).
- **Styles**: `block`, `title`, `ctas`, `carousel`.

### Breadcrumb — `breadcrumb`

A navigation trail. Carries **no** `ctas`.

| Prop | Type | Notes |
|------|------|-------|
| `items` | `BreadcrumbItemData[]` | `label` (req, ≤255), `url?` (≤2048) |

- **Styles**: `block`.

### Map — `map`

A map framed on a coordinate with optional pins (`MapMarkerData`).

| Prop | Type | Notes |
|------|------|-------|
| `latitude` | float | `-90..90` (default `0`) |
| `longitude` | float | `-180..180` (default `0`) |
| `zoom` | int | `0..22` (default `12`) |
| `provider` | string? | map provider hint (≤255) |
| `markers` | `MapMarkerData[]` | `latitude`, `longitude`, `label?`, `description?`, `icon?` |

- **Styles**: `block`, `title`, `ctas`.

### Table — `table`

A simple data table; the caption is the `name` column.

| Prop | Type | Notes |
|------|------|-------|
| `headers` | `string[]` | column headers |
| `rows` | `string[][]` | row cells |

- **Styles**: `block`, `title`, `ctas`.

### Warranty — `warranty`

An introduction with a cover image and a list of warranty items.

| Prop | Type | Notes |
|------|------|-------|
| `pre` | string? | kicker shown above the title (≤255) |
| `items` | `WarrantyItemData[]` | `title` (req, ≤255), `text?`, `icon?` (≤255) |

- **Media**: `cover` attachment (illustration).
- **Styles**: `block`, `title`, `excerpt`, `ctas`, `list`.

### FAQ — `faqs`

A list of question/answer pairs (`FaqItemData`). Carries **no** `ctas`.

| Prop | Type | Notes |
|------|------|-------|
| `items` | `FaqItemData[]` | `question` (req, ≤255), `answer` (req, markdown) |

- **Styles**: `block`, `title`, `excerpt`.
