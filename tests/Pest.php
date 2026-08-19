<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use OiLab\OiLaravelPublish\Data\PublishTemplateData;
use OiLab\OiLaravelPublish\Enums\PublishTemplateType;
use OiLab\OiLaravelPublish\Models\PublishBlock;
use OiLab\OiLaravelPublish\Models\PublishPage;
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

/**
 * A block whose stored props are exactly what is given, old names included.
 *
 * The props go in through the query builder rather than the model: `PropsCast`
 * would hydrate them into the template's *current* class and drop every name a
 * migration is written to rename, before the migration could read it.
 *
 * @param  array<string, mixed>  $props
 */
function blockWithRawProps(string $templateKey, array $props): PublishBlock
{
    $page = PublishPage::factory()->create();

    $block = PublishBlock::factory()->forPage($page)->template($templateKey)->create();

    DB::table($block->getTable())
        ->where('id', $block->getKey())
        ->update(['props' => json_encode($props)]);

    return $block;
}

/**
 * The stored props of a block, straight out of the column — the only reading
 * that can tell a migrated block from an unmigrated one.
 *
 * @return array<string, mixed>
 */
function rawProps(PublishBlock $block): array
{
    $json = DB::table($block->getTable())->where('id', $block->getKey())->value('props');

    return json_decode((string) $json, true) ?? [];
}
