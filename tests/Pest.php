<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use OiLab\OiLaravelPublish\Data\PublishTemplateData;
use OiLab\OiLaravelPublish\Enums\PublishTemplateType;
use OiLab\OiLaravelPublish\OiLaravelPublish;
use OiLab\OiLaravelPublish\Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class)->in(__DIR__);

/**
 * Register a page template declaring no `propsClass`, the way a host application
 * defines one. Both bundled page templates are typed, so this is what the
 * generic `GenericPropsData` fallback is exercised against.
 */
function typelessPageTemplate(string $key): PublishTemplateData
{
    $template = new PublishTemplateData(
        key: $key,
        name: 'Typeless page',
        type: PublishTemplateType::Page,
    );

    OiLaravelPublish::registry()->register($template);

    return $template;
}
