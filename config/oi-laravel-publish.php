<?php

use OiLab\OiLaravelPublish\Data\Blocks\BlockquoteData;
use OiLab\OiLaravelPublish\Data\Blocks\BreadcrumbData;
use OiLab\OiLaravelPublish\Data\Blocks\ContentData;
use OiLab\OiLaravelPublish\Data\Blocks\FaqsData;
use OiLab\OiLaravelPublish\Data\Blocks\FormData;
use OiLab\OiLaravelPublish\Data\Blocks\GridData;
use OiLab\OiLaravelPublish\Data\Blocks\HeroData;
use OiLab\OiLaravelPublish\Data\Blocks\MapData;
use OiLab\OiLaravelPublish\Data\Blocks\SlidesData;
use OiLab\OiLaravelPublish\Data\Blocks\StoryData;
use OiLab\OiLaravelPublish\Data\Blocks\TableData;
use OiLab\OiLaravelPublish\Data\Blocks\WarrantyData;
use OiLab\OiLaravelPublish\Data\Items\FaqItemData;
use OiLab\OiLaravelPublish\Data\Items\GridItemData;
use OiLab\OiLaravelPublish\Data\Items\MapMarkerData;
use OiLab\OiLaravelPublish\Data\Items\SlideItemData;
use OiLab\OiLaravelPublish\Data\Items\StoryItemData;
use OiLab\OiLaravelPublish\Data\Items\WarrantyItemData;
use OiLab\OiLaravelPublish\Data\Pages\PagePropsData;
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
    | a single `cover`; blocks own a `cover`, an ordered `slides` gallery, and a
    | `gallery` collection.
    |
    */
    'attachments' => [
        'page' => ['cover'],
        'block' => ['cover', 'video', 'slides', 'gallery'],
        'max_files' => 30,
        'max_file_size' => 10240, // kilobytes
        // A video is measured in tens of megabytes where an image is measured in
        // hundreds of kilobytes; one ceiling for both would either refuse every
        // video or accept a 100 MB "image".
        'max_video_file_size' => 102400, // kilobytes
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
    |   propsClass   typed PropsData subclass used by the PropsCast
    |   allowedBlocks (page templates) ordered block keys allowed inside
    |   requiresName whether a `name` must be given (defaults to true), and never
    |                against the capabilities: a block that renders no title
    |                cannot require a name
    |   capabilities (block templates) what the block renders — see
    |                BlockCapabilitiesData. Declared as an associative array, not an
    |                instance: `config:cache` serialises this file
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
            'propsClass' => PagePropsData::class,
            'allowedBlocks' => [
                'hero',
                'grid',
                'story',
                'content',
                'blockquote',
                'slides',
                'form',
                'map',
                'table',
                'warranty',
                'faqs',
                'breadcrumb',
            ],
        ],
        [
            'key' => 'landing',
            'name' => 'Landing page',
            'type' => PublishTemplateType::Page->value,
            'description' => 'A marketing landing page leading with a hero.',
            'propsClass' => PagePropsData::class,
            'allowedBlocks' => [
                'hero',
                'grid',
                'slides',
                'content',
                'form',
                'faqs',
            ],
        ],

        // --- Block templates ------------------------------------------------
        [
            'key' => 'hero',
            'name' => 'En-tête',
            'type' => PublishTemplateType::Block->value,
            'description' => 'Full-width headline with call to action and cover image.',
            'propsClass' => HeroData::class,
            'capabilities' => ['pre' => true, 'title' => true, 'excerpt' => true, 'body' => true, 'media' => ['cover'], 'ctas' => true],
        ],
        [
            'key' => 'grid',
            'name' => 'Grille',
            'type' => PublishTemplateType::Block->value,
            'description' => 'A grid of items or selling points.',
            'propsClass' => GridData::class,
            // Seeds only what differs from the DTO defaults: one column on mobile,
            // three from the `md` breakpoint up.
            'props' => ['styles' => ['list' => ['columns' => ['base' => 1, 'md' => 3]]]],
            'capabilities' => ['pre' => true, 'title' => true, 'excerpt' => true, 'media' => ['gallery'], 'itemsClass' => GridItemData::class, 'ctas' => true],
        ],
        [
            'key' => 'blockquote',
            'name' => 'Citation',
            'type' => PublishTemplateType::Block->value,
            'description' => 'A highlighted quotation with attribution.',
            'propsClass' => BlockquoteData::class,
            'capabilities' => ['body' => true, 'ctas' => true],
        ],
        [
            'key' => 'content',
            'name' => 'Contenu',
            'type' => PublishTemplateType::Block->value,
            'description' => 'Free-form rich text rendered with the configured renderer.',
            'propsClass' => ContentData::class,
            'capabilities' => ['pre' => true, 'title' => true, 'excerpt' => true, 'body' => true, 'media' => ['cover', 'video'], 'ctas' => true],
        ],
        [
            'key' => 'form',
            'name' => 'Formulaire',
            'type' => PublishTemplateType::Block->value,
            'description' => 'Embeds a host-application form by key.',
            'propsClass' => FormData::class,
            'capabilities' => ['pre' => true, 'title' => true, 'excerpt' => true, 'ctas' => true],
        ],
        [
            'key' => 'slides',
            'name' => 'Slides',
            'type' => PublishTemplateType::Block->value,
            'description' => 'A carousel backed by the `slides` attachment collection.',
            'propsClass' => SlidesData::class,
            'capabilities' => ['pre' => true, 'title' => true, 'excerpt' => true, 'media' => ['slides'], 'itemsClass' => SlideItemData::class, 'ctas' => true],
        ],
        [
            'key' => 'breadcrumb',
            'name' => 'Fil d\'Ariane',
            'type' => PublishTemplateType::Block->value,
            'description' => 'A breadcrumb trail.',
            'propsClass' => BreadcrumbData::class,
            'capabilities' => [],
        ],
        [
            'key' => 'map',
            'name' => 'Carte',
            'type' => PublishTemplateType::Block->value,
            'description' => 'A map centred on a coordinate.',
            'propsClass' => MapData::class,
            'capabilities' => ['pre' => true, 'title' => true, 'excerpt' => true, 'itemsClass' => MapMarkerData::class, 'itemsProperty' => 'markers', 'ctas' => true],
        ],
        [
            'key' => 'table',
            'name' => 'Table',
            'type' => PublishTemplateType::Block->value,
            'description' => 'A simple data table.',
            'propsClass' => TableData::class,
            'capabilities' => ['pre' => true, 'title' => true, 'excerpt' => true, 'ctas' => true],
        ],
        [
            'key' => 'warranty',
            'name' => 'Garanties',
            'type' => PublishTemplateType::Block->value,
            'description' => 'An introduction with a cover image and a list of warranty items.',
            'propsClass' => WarrantyData::class,
            'props' => ['pre' => ''],
            'capabilities' => ['pre' => true, 'title' => true, 'excerpt' => true, 'media' => ['cover'], 'itemsClass' => WarrantyItemData::class, 'ctas' => true],
        ],
        [
            'key' => 'story',
            'name' => 'Histoire',
            'type' => PublishTemplateType::Block->value,
            'description' => 'A connected sequence of steps laid out along a central rail.',
            'propsClass' => StoryData::class,
            'capabilities' => ['pre' => true, 'title' => true, 'excerpt' => true, 'body' => true, 'media' => ['gallery'], 'itemsClass' => StoryItemData::class, 'ctas' => true],
        ],
        [
            'key' => 'faqs',
            'name' => 'FAQ',
            'type' => PublishTemplateType::Block->value,
            'description' => 'A list of questions with markdown answers.',
            'propsClass' => FaqsData::class,
            'capabilities' => ['pre' => true, 'title' => true, 'excerpt' => true, 'itemsClass' => FaqItemData::class, 'ctas' => true],
        ],
    ],
];
