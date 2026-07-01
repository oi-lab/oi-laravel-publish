<?php

use OiLab\OiLaravelPublish\Data\Blocks\ContentData;
use OiLab\OiLaravelPublish\Data\Blocks\HeroData;
use OiLab\OiLaravelPublish\Data\GenericPropsData;
use OiLab\OiLaravelPublish\Models\PublishBlock;
use OiLab\OiLaravelPublish\Models\PublishPage;

it('casts props to the generic bag when the template declares no typed class', function () {
    $page = PublishPage::factory()->create(['props' => ['foo' => 'bar', 'n' => 3]]);
    $page->refresh();

    expect($page->props)->toBeInstanceOf(GenericPropsData::class)
        ->and($page->props->value('foo'))->toBe('bar')
        ->and($page->props->value('n'))->toBe(3)
        ->and($page->props->value('missing', 'fallback'))->toBe('fallback');
});

it('casts props to the typed class declared by the template', function () {
    $page = PublishPage::factory()->create();
    $block = PublishBlock::factory()->forPage($page)->hero()->create();
    $block->refresh();

    expect($block->props)->toBeInstanceOf(HeroData::class)
        ->and($block->props->alignment)->toBe('center')
        ->and($block->props->heading)->not->toBeNull();
});

it('persists a typed Data object assigned to props', function () {
    $page = PublishPage::factory()->create();
    $block = PublishBlock::factory()->forPage($page)->template('content')->create([
        'props' => new ContentData(body: 'Hello world', format: 'markdown'),
    ]);
    $block->refresh();

    expect($block->props)->toBeInstanceOf(ContentData::class)
        ->and($block->props->body)->toBe('Hello world')
        ->and($block->props->format)->toBe('markdown');
});

it('round-trips raw props through the json column', function () {
    $page = PublishPage::factory()->create();
    $block = PublishBlock::factory()->forPage($page)->template('content')->create([
        'props' => ['body' => 'Stored as array'],
    ]);

    $stored = $block->getRawOriginal('props');

    expect($stored)->toBeString()
        ->and(json_decode($stored, true))->toMatchArray(['body' => 'Stored as array']);

    $block->refresh();
    expect($block->props->body)->toBe('Stored as array');
});
