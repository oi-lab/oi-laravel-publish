<?php

namespace OiLab\OiLaravelPublish;

use OiLab\OiLaravelPublish\Data\PublishTemplateData;
use OiLab\OiLaravelPublish\Enums\PublishTemplateType;
use OiLab\OiLaravelPublish\Models\PublishBlock;
use OiLab\OiLaravelPublish\Models\PublishPage;
use OiLab\OiLaravelPublish\Support\PublishTemplateRegistry;

/**
 * OiLaravelPublish
 *
 * Static resolver for the package's configurable collaborators (models, user
 * model, template registry, renderers). Always resolve through this helper so a
 * host application can override classes and settings via config.
 */
class OiLaravelPublish
{
    public static function userModel(): string
    {
        return config('oi-laravel-publish.user_model', 'App\\Models\\User');
    }

    /**
     * @return class-string<PublishPage>
     */
    public static function pageModel(): string
    {
        return config('oi-laravel-publish.models.page', PublishPage::class);
    }

    /**
     * @return class-string<PublishBlock>
     */
    public static function blockModel(): string
    {
        return config('oi-laravel-publish.models.block', PublishBlock::class);
    }

    public static function registry(): PublishTemplateRegistry
    {
        return app(PublishTemplateRegistry::class);
    }

    public static function template(string $key): ?PublishTemplateData
    {
        return static::registry()->get($key);
    }

    /**
     * @return array<string, PublishTemplateData>
     */
    public static function templates(?PublishTemplateType $type = null): array
    {
        $registry = static::registry();

        return $type === null ? $registry->all() : $registry->byType($type);
    }

    /**
     * @return array<string, PublishTemplateData>
     */
    public static function pageTemplates(): array
    {
        return static::templates(PublishTemplateType::Page);
    }

    /**
     * @return array<string, PublishTemplateData>
     */
    public static function blockTemplates(): array
    {
        return static::templates(PublishTemplateType::Block);
    }

    /**
     * The renderer used for page descriptions (e.g. "markdown"), resolved from
     * the host Setting model when available, otherwise from config.
     */
    public static function pageDescriptionRenderer(): string
    {
        return static::descriptionRenderer('PUBLISH.PAGE_DESCRIPTION_RENDERER', 'page');
    }

    /**
     * The renderer used for block descriptions.
     */
    public static function blockDescriptionRenderer(): string
    {
        return static::descriptionRenderer('PUBLISH.BLOCK_DESCRIPTION_RENDERER', 'block');
    }

    protected static function descriptionRenderer(string $settingKey, string $configKey): string
    {
        $default = (string) config("oi-laravel-publish.renderers.{$configKey}", 'markdown');

        return app(Support\SettingResolver::class)->get($settingKey, $default) ?? $default;
    }
}
