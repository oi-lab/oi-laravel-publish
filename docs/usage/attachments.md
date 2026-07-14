---
title: Attachments
description: Cover and slides media collections for pages and blocks.
order: 5
---

# Attachments

Pages and blocks use `oi-lab/oi-laravel-attachments` for media. The collections
are:

- **Pages**: `cover` (single image).
- **Blocks**: `cover` (single image) and `slides` (ordered gallery for the
  `slides` carousel template; each slide links one entry by `attachment_uuid`).

## Attaching files

```php
$page->attachFile($file, 'cover');
$page->cover;                  // MorphOne attachment
$page->cover?->file;           // the File model

$block->attachFile($file, 'cover');
$block->syncAttachments([$slideA, $slideB], 'slides'); // ordered
$block->slides()->get();       // ordered slides
```

## Storing uploads

Use the attachments package action to store an upload and attach it in one step:

```php
use OiLab\OiLaravelAttachments\Actions\AttachUploadedFiles;

AttachUploadedFiles::handle($block, $request->file('slides') ?? [], 'slides');

if ($request->file('cover')) {
    AttachUploadedFiles::handle($block, [$request->file('cover')], 'cover');
}
```

The [form requests](../configuration/configuration.md) validate `cover` and
`slides` uploads for you.

## Configuring collections

The collections and upload limits are configurable under the `attachments` key:

```php
'attachments' => [
    'page'  => ['cover'],
    'block' => ['cover', 'slides'],
    'max_files' => 30,
    'max_file_size' => 10240, // kilobytes
],
```
