---
title: Templates
description: Code-defined page and block templates and the template registry.
order: 3
---

# Templates

A `PublishTemplate` describes how a page or block is processed and rendered. It
is a **static, code-defined descriptor** — not a database table. Pages and blocks
reference one by its string `template_key`.

Templates live in `config('oi-laravel-publish.templates')` and are hydrated into
the `PublishTemplateRegistry`.

## Anatomy

```php
[
    'key'          => 'hero',
    'name'         => 'Hero',
    'type'         => PublishTemplateType::Block->value, // 'page' | 'block'
    'description'  => 'Full-width headline with a call to action.',
    'propsClass'   => HeroData::class,   // typed props (blocks)
    'props'        => ['styles' => ['title' => ['align' => 'left']]], // default props
    // page templates may also declare:
    'allowedBlocks' => ['hero', 'features', 'content'],
],
```

## Resolving templates

```php
use OiLab\OiLaravelPublish\OiLaravelPublish;
use OiLab\OiLaravelPublish\Enums\PublishTemplateType;

OiLaravelPublish::template('hero');        // ?PublishTemplateData
OiLaravelPublish::pageTemplates();         // array<string, PublishTemplateData>
OiLaravelPublish::blockTemplates();
OiLaravelPublish::registry()->keys(PublishTemplateType::Block);
```

## Bundled templates

Page templates: `default`, `landing`.

Block templates: `hero`, `features`, `blockquote`, `content`, `form`, `slides`,
`breadcrumb`, `map`, `table`.

## Adding templates

Add an entry to the config array, or register one at runtime (e.g. from a host
service provider):

```php
use OiLab\OiLaravelPublish\Data\PublishTemplateData;
use OiLab\OiLaravelPublish\Enums\PublishTemplateType;
use OiLab\OiLaravelPublish\OiLaravelPublish;

OiLaravelPublish::registry()->register(new PublishTemplateData(
    key: 'pricing',
    name: 'Pricing table',
    type: PublishTemplateType::Block,
    propsClass: \App\Publish\PricingData::class,
));
```
