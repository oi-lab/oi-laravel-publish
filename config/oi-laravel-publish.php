<?php

use OiLab\OiLaravelPublish\Data\Blocks\BlockquoteData;
use OiLab\OiLaravelPublish\Data\Blocks\BreadcrumbData;
use OiLab\OiLaravelPublish\Data\Blocks\ContentData;
use OiLab\OiLaravelPublish\Data\Blocks\FeaturesData;
use OiLab\OiLaravelPublish\Data\Blocks\FormData;
use OiLab\OiLaravelPublish\Data\Blocks\HeroData;
use OiLab\OiLaravelPublish\Data\Blocks\MapData;
use OiLab\OiLaravelPublish\Data\Blocks\SlidesData;
use OiLab\OiLaravelPublish\Data\Blocks\TableData;
use OiLab\OiLaravelPublish\Data\Blocks\WarrantyData;
use OiLab\OiLaravelPublish\Enums\PublishTemplateType;
use OiLab\OiLaravelPublish\Models\PublishBlock;
use OiLab\OiLaravelPublish\Models\PublishPage;

return [

    /*
    |--------------------------------------------------------------------------
    | User Model
    |--------------------------------------------------------------------------
    |
    | The model used for the created_by / updated_by audit relationships,
    | inherited from oi-laravel-attachments' HasCreatorAndUpdater concern.
    |
    */
    'user_model' => 'App\\Models\\User',

    /*
    |--------------------------------------------------------------------------
    | Models
    |--------------------------------------------------------------------------
    |
    | The model classes used by the package. Override these with your own
    | classes (extending the package base models) to customize behavior. Always
    | resolve them through the OiLaravelPublish helper so overrides keep working.
    |
    */
    'models' => [
        'page' => PublishPage::class,
        'block' => PublishBlock::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Attachment Collections
    |--------------------------------------------------------------------------
    |
    | The named oi-laravel-attachments collections each model exposes. Pages own
    | a single `cover`; blocks own a `cover` plus an ordered `slides` gallery.
    |
    */
    'attachments' => [
        'page' => ['cover'],
        'block' => ['cover', 'slides'],
        'max_files' => 30,
        'max_file_size' => 10240, // kilobytes
    ],

    /*
    |--------------------------------------------------------------------------
    | Description Renderers
    |--------------------------------------------------------------------------
    |
    | How the `description` field of pages and blocks should be rendered. These
    | are the package defaults; when a host Setting model is present the matching
    | PUBLISH.* settings (see below) take precedence.
    |
    */
    'renderers' => [
        'page' => 'markdown',
        'block' => 'markdown',
    ],

    /*
    |--------------------------------------------------------------------------
    | Setting Model Integration
    |--------------------------------------------------------------------------
    |
    | When the host application exposes a key/value Setting model, the package
    | seeds the renderer settings into it (via `publish:install-settings`) and
    | resolves them from it at runtime. Everything no-ops gracefully and falls
    | back to the `renderers` map above when the model is absent.
    |
    */
    'settings' => [
        // Explicit SettingStore implementation (class-string). Leave null to
        // auto-detect: the oi-lab/oi-laravel-settings adapter is used when that
        // package is installed, otherwise the generic key/value model below.
        'store' => env('OI_PUBLISH_SETTING_STORE'),

        'model' => 'App\\Models\\Setting',
        'key_column' => 'key',
        'value_column' => 'value',

        'defaults' => [
            'PUBLISH.PAGE_DESCRIPTION_RENDERER' => 'markdown',
            'PUBLISH.BLOCK_DESCRIPTION_RENDERER' => 'markdown',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Templates
    |--------------------------------------------------------------------------
    |
    | The static catalogue of page and block templates, hydrated into the
    | PublishTemplateRegistry. Each entry maps to a PublishTemplateData:
    |
    |   key          unique string referenced by pages/blocks (template_key)
    |   name         human label
    |   type         PublishTemplateType::Page or ::Block
    |   description  optional help text
    |   props        default props applied to new pages/blocks
    |   propsClass   typed PropsData subclass used by the PropsCast (blocks)
    |   allowedBlocks (page templates) ordered block keys allowed inside
    |
    | Add or override templates here, or at runtime via
    | OiLaravelPublish::registry()->register(...).
    |
    */
    'templates' => [

        // --- Page templates -------------------------------------------------
        [
            'key' => 'default',
            'name' => 'Default page',
            'type' => PublishTemplateType::Page->value,
            'description' => 'A standard content page composed of ordered blocks.',
            'allowedBlocks' => [
                'hero', 'features', 'content', 'blockquote', 'slides',
                'form', 'breadcrumb', 'map', 'table', 'warranty',
            ],
        ],
        [
            'key' => 'landing',
            'name' => 'Landing page',
            'type' => PublishTemplateType::Page->value,
            'description' => 'A marketing landing page leading with a hero.',
            'allowedBlocks' => ['hero', 'features', 'slides', 'content', 'form'],
        ],

        // --- Block templates ------------------------------------------------
        [
            'key' => 'hero',
            'name' => 'Hero',
            'type' => PublishTemplateType::Block->value,
            'description' => 'Full-width headline with call to action and cover image.',
            'propsClass' => HeroData::class,
            'props' => ['alignment' => 'left'],
        ],
        [
            'key' => 'features',
            'name' => 'Features',
            'type' => PublishTemplateType::Block->value,
            'description' => 'A grid of features or selling points.',
            'propsClass' => FeaturesData::class,
            'props' => ['columns' => 3],
        ],
        [
            'key' => 'blockquote',
            'name' => 'Blockquote',
            'type' => PublishTemplateType::Block->value,
            'description' => 'A highlighted quotation with attribution.',
            'propsClass' => BlockquoteData::class,
        ],
        [
            'key' => 'content',
            'name' => 'Content',
            'type' => PublishTemplateType::Block->value,
            'description' => 'Free-form rich text rendered with the configured renderer.',
            'propsClass' => ContentData::class,
            'props' => ['format' => 'markdown'],
        ],
        [
            'key' => 'form',
            'name' => 'Form',
            'type' => PublishTemplateType::Block->value,
            'description' => 'Embeds a host-application form by key.',
            'propsClass' => FormData::class,
            'props' => ['method' => 'post'],
        ],
        [
            'key' => 'slides',
            'name' => 'Slides',
            'type' => PublishTemplateType::Block->value,
            'description' => 'A carousel backed by the `slides` attachment collection.',
            'propsClass' => SlidesData::class,
            'props' => ['autoplay' => false, 'interval' => 5000, 'loop' => true],
        ],
        [
            'key' => 'breadcrumb',
            'name' => 'Breadcrumb',
            'type' => PublishTemplateType::Block->value,
            'description' => 'A breadcrumb trail.',
            'propsClass' => BreadcrumbData::class,
        ],
        [
            'key' => 'map',
            'name' => 'Map',
            'type' => PublishTemplateType::Block->value,
            'description' => 'A map centred on a coordinate.',
            'propsClass' => MapData::class,
            'props' => ['zoom' => 12],
        ],
        [
            'key' => 'table',
            'name' => 'Table',
            'type' => PublishTemplateType::Block->value,
            'description' => 'A simple data table.',
            'propsClass' => TableData::class,
        ],
        [
            'key' => 'warranty',
            'name' => 'Warranty',
            'type' => PublishTemplateType::Block->value,
            'description' => 'An introduction with a cover image and a list of warranty items.',
            'propsClass' => WarrantyData::class,
            'props' => ['pre' => '', 'title' => '', 'overview' => ''],
        ],
    ],
];
