---
title: Usage
description: Working with pages, blocks, templates, typed props and attachments.
order: 2
---

# Usage

OI Laravel Publish revolves around these ideas:

- [Pages](./pages.md) — recursive `PublishPage` models forming a content tree.
- [Blocks](./blocks.md) — an ordered, flat collection of `PublishBlock`s per page.
- [Templates](./templates.md) — code-defined descriptors referenced by `template_key`.
- [Typed props](./props.md) — spatie/laravel-data props for pages and blocks.
- [Attachments](./attachments.md) — `cover` and `slides` media collections.

Resolve models and templates through `OiLab\OiLaravelPublish\OiLaravelPublish`
so configuration overrides keep working.
