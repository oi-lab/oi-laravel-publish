---
title: TypeScript
description: Generate typed TypeScript interfaces for pages, blocks and typed props via oi-laravel-ts.
order: 2
---

# TypeScript

The package's `spatie/laravel-data` objects are designed to generate clean
TypeScript through `oi-lab/oi-laravel-ts`. Because the Eloquent models live in
the package (not `app/Models`), the **DTOs** are the frontend contract — the same
pattern `oi-laravel-ts` uses elsewhere.

## Enable generation

Add the package's `Data` namespace to `data_namespaces` in the host app's
`config/oi-laravel-ts.php`:

```php
'data_namespaces' => [
    'App\\Data',
    'OiLab\\OiLaravelPublish\\Data',
],
```

Then regenerate:

```bash
php artisan oi:gen-ts
```

## What gets generated

- `IPublishPageData`, `IPublishBlockData`, `IPublishTemplateData`.
- Every typed block props interface: `IHeroData`, `IGridData`
  (+ `IGridItemData`), `IBlockquoteData`, `IContentData`, `IFormData`,
  `ISlidesData` (+ `ISlideItemData`), `IBreadcrumbData`
  (+ `IBreadcrumbItemData`), `IMapData`, `ITableData`.

## Typed `props`

`PublishBlockData.props` is emitted as a **discriminated union** of the typed
block interfaces (driven by the DTO's `@param` union), so the frontend can narrow
by `template_key`:

```ts
props: IHeroData | IGridData | IBlockquoteData | IContentData
     | IFormData | ISlidesData | IBreadcrumbData | IMapData | ITableData
     | Record<string, unknown>;
```

`PublishPageData.props` is `Record<string, unknown>` (pages use generic props).

The DTOs carry `props` as a flat map at runtime, so the JSON matches these
interfaces exactly. When you add a typed block (a new `Data/Blocks/<Name>Data`
extending `PropsData`), add it to the `@param` union on `PublishBlockData` to have
it appear in the generated union.
