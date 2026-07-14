<?php

use Illuminate\Database\QueryException;
use Illuminate\Validation\ValidationException;
use OiLab\OiLaravelAttachments\Models\File;
use OiLab\OiLaravelPublish\Data\Blocks\ContentData;
use OiLab\OiLaravelPublish\Data\Blocks\FaqItemData;
use OiLab\OiLaravelPublish\Data\Blocks\FaqsData;
use OiLab\OiLaravelPublish\Data\Blocks\FeatureItemData;
use OiLab\OiLaravelPublish\Data\Blocks\FeaturesData;
use OiLab\OiLaravelPublish\Data\Blocks\HeroData;
use OiLab\OiLaravelPublish\Data\Blocks\SlidesData;
use OiLab\OiLaravelPublish\Data\Blocks\WarrantyData;
use OiLab\OiLaravelPublish\Data\Blocks\WarrantyItemData;
use OiLab\OiLaravelPublish\Enums\CoverLayout;
use OiLab\OiLaravelPublish\Enums\MediaRatio;
use OiLab\OiLaravelPublish\Models\PublishBlock;
use OiLab\OiLaravelPublish\Models\PublishPage;

it('belongs to a page', function () {
    $page = PublishPage::factory()->create();
    $block = PublishBlock::factory()->forPage($page)->create();

    expect($block->page->is($page))->toBeTrue();
});

it('rejects a duplicate key within the same page', function () {
    $page = PublishPage::factory()->create();
    PublishBlock::factory()->forPage($page)->create(['key' => 'intro']);

    PublishBlock::factory()->forPage($page)->create(['key' => 'intro']);
})->throws(QueryException::class);

it('allows the same key across different pages', function () {
    $a = PublishPage::factory()->create();
    $b = PublishPage::factory()->create();

    PublishBlock::factory()->forPage($a)->create(['key' => 'intro']);
    PublishBlock::factory()->forPage($b)->create(['key' => 'intro']);

    expect(PublishBlock::where('key', 'intro')->count())->toBe(2);
});

it('owns cover and slides attachment collections', function () {
    $page = PublishPage::factory()->create();
    $block = PublishBlock::factory()->forPage($page)->template('slides')->create([
        'props' => ['autoplay' => true],
    ]);

    $cover = File::factory()->create();
    $slideOne = File::factory()->create();
    $slideTwo = File::factory()->create();

    $block->attachFile($cover, 'cover');
    $block->syncAttachments([$slideOne, $slideTwo], 'slides');

    expect($block->cover->file->is($cover))->toBeTrue()
        ->and($block->slides()->get())->toHaveCount(2)
        ->and($block->slides()->get()->pluck('file_id')->all())->toBe([$slideOne->id, $slideTwo->id]);
});

it('creates a warranty block with typed props and items', function () {
    $page = PublishPage::factory()->create();
    $block = PublishBlock::factory()->forPage($page)->warranty()->create(['key' => 'warranty']);

    expect($block->template_key)->toBe('warranty')
        ->and($block->props)->toBeInstanceOf(WarrantyData::class)
        ->and($block->props->pre)->toBeString()
        ->and($block->props->items)->toHaveCount(2)
        ->and($block->props->items[0])->toBeInstanceOf(WarrantyItemData::class)
        ->and($block->props->items[0]->title)->toBeString()
        ->and($block->props->items[1]->text)->toBeNull();
});

it('creates a faqs block with typed props and items', function () {
    $page = PublishPage::factory()->create();
    $block = PublishBlock::factory()->forPage($page)->faqs()->create(['key' => 'faqs']);

    expect($block->template_key)->toBe('faqs')
        ->and($block->props)->toBeInstanceOf(FaqsData::class)
        ->and($block->props->items)->toHaveCount(2)
        ->and($block->props->items[0])->toBeInstanceOf(FaqItemData::class)
        ->and($block->props->items[0]->question)->toBeString()
        ->and($block->props->items[0]->answer)->toBeString();
});

it('defaults the cover layout to the right on cover blocks', function () {
    expect(HeroData::from([])->cover_layout)->toBe(CoverLayout::Right)
        ->and(FeaturesData::from([])->cover_layout)->toBe(CoverLayout::Right)
        ->and(ContentData::from([])->cover_layout)->toBe(CoverLayout::Right);
});

it('carries a pre kicker and cover layout on a content block', function () {
    $content = ContentData::from(['pre' => 'Intro', 'cover_layout' => 'background']);

    expect($content->pre)->toBe('Intro')
        ->and($content->format)->toBe('markdown')
        ->and($content->cover_layout)->toBe(CoverLayout::Background);
});

it('owns a cover image on a warranty block', function () {
    $page = PublishPage::factory()->create();
    $block = PublishBlock::factory()->forPage($page)->warranty()->create(['key' => 'warranty']);

    $cover = File::factory()->create();
    $block->attachFile($cover, 'cover');

    expect($block->cover->file->is($cover))->toBeTrue();
});

it('converts to a data object', function () {
    $page = PublishPage::factory()->create();
    $block = PublishBlock::factory()->forPage($page)->hero()->create(['key' => 'hero']);

    $data = $block->toData();

    $props = $data->toArray()['props'];

    expect($data->key)->toBe('hero')
        ->and($data->template_key)->toBe('hero')
        ->and($data->props)->toBeArray()
        ->and($props)->toHaveKey('styles')
        ->and($props['styles']['title']['align'])->toBe('center')
        ->and($props['ctas'][0])->toHaveKeys(['label', 'url', 'target', 'variant', 'size', 'position']);
});

it('builds a slides block from its factory state', function () {
    $page = PublishPage::factory()->create();
    $block = PublishBlock::factory()->forPage($page)->slides()->create();

    expect($block->template_key)->toBe('slides')
        ->and($block->props)->toBeInstanceOf(SlidesData::class)
        ->and($block->props->media_ratio)->toBe(MediaRatio::Widescreen)
        ->and($block->props->items)->toHaveCount(2)
        ->and($block->props->items[0]->attachment_uuid)->toBeNull();
});

it('carries an optional cover and ratio on a feature item', function () {
    $item = FeatureItemData::from([
        'title' => 'Fast',
        'attachment_uuid' => '9b7c1e2a-4f56-4c3d-8a1b-2e3f4a5b6c7d',
        'cover_ratio' => 'widescreen',
    ]);

    expect($item->attachment_uuid)->toBe('9b7c1e2a-4f56-4c3d-8a1b-2e3f4a5b6c7d')
        ->and($item->cover_ratio)->toBe(MediaRatio::Widescreen)
        // Defaults: no cover, ratio inherits from the block.
        ->and(FeatureItemData::from(['title' => 'Plain'])->attachment_uuid)->toBeNull()
        ->and(FeatureItemData::from(['title' => 'Plain'])->cover_ratio)->toBe(MediaRatio::Inherit);
});

it('rejects a feature item attachment_uuid longer than 36 chars', function () {
    FeatureItemData::validate(['title' => 'X', 'attachment_uuid' => str_repeat('a', 37)]);
})->throws(ValidationException::class);

it('round-trips feature item covers through the json props column', function () {
    $page = PublishPage::factory()->create();
    $block = PublishBlock::factory()->forPage($page)->template('features')->create([
        'props' => ['items' => [
            ['title' => 'Fast', 'attachment_uuid' => '9b7c1e2a-4f56-4c3d-8a1b-2e3f4a5b6c7d', 'cover_ratio' => 'square'],
            ['title' => 'Simple'],
        ]],
    ]);

    $block->refresh();

    expect($block->props)->toBeInstanceOf(FeaturesData::class)
        ->and($block->props->items[0]->attachment_uuid)->toBe('9b7c1e2a-4f56-4c3d-8a1b-2e3f4a5b6c7d')
        ->and($block->props->items[0]->cover_ratio)->toBe(MediaRatio::Square)
        ->and($block->props->items[1]->attachment_uuid)->toBeNull();
});
