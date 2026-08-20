<?php

use Illuminate\Database\QueryException;
use Illuminate\Validation\ValidationException;
use OiLab\OiLaravelAttachments\Models\File;
use OiLab\OiLaravelPublish\Data\Blocks\ContentData;
use OiLab\OiLaravelPublish\Data\Blocks\FaqsData;
use OiLab\OiLaravelPublish\Data\Blocks\GridData;
use OiLab\OiLaravelPublish\Data\Blocks\HeroData;
use OiLab\OiLaravelPublish\Data\Blocks\SlidesData;
use OiLab\OiLaravelPublish\Data\Blocks\WarrantyData;
use OiLab\OiLaravelPublish\Data\Items\FaqItemData;
use OiLab\OiLaravelPublish\Data\Items\GridItemData;
use OiLab\OiLaravelPublish\Data\Items\SlideItemData;
use OiLab\OiLaravelPublish\Data\Items\StoryItemData;
use OiLab\OiLaravelPublish\Data\Items\WarrantyItemData;
use OiLab\OiLaravelPublish\Enums\CoverLayout;
use OiLab\OiLaravelPublish\Enums\ItemLayout;
use OiLab\OiLaravelPublish\Enums\MediaRatio;
use OiLab\OiLaravelPublish\Models\PublishBlock;
use OiLab\OiLaravelPublish\Models\PublishPage;
use OiLab\OiLaravelPublish\Support\BlockContract;

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
        ->and($block->props->items[0]->title)->toBeString()
        ->and($block->props->items[0]->text)->toBeString();
});

it('defaults the cover layout to the right when only the ratio is overridden', function () {
    expect(HeroData::from(['styles' => ['media' => ['ratio' => 'square']]])->styles->media->layout)
        ->toBe(CoverLayout::Right)
        ->and(ContentData::from(['styles' => ['media' => ['ratio' => 'square']]])->styles->media->layout)
        ->toBe(CoverLayout::Right)
        // A grid block's media is null until overridden, same as every other
        // block's now — its images are the pool its items draw from, so
        // nothing in the console ever offers it a layout to set.
        ->and(GridData::from([])->styles->media)->toBeNull();
});

it('carries a pre kicker and cover layout on a content block', function () {
    $content = ContentData::from(['pre' => 'Intro', 'styles' => ['media' => ['layout' => 'background']]]);

    expect($content->pre)->toBe('Intro')
        ->and($content->format)->toBe('markdown')
        ->and($content->styles->media->layout)->toBe(CoverLayout::Background);
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
        ->and($block->props->styles->media->ratio)->toBe(MediaRatio::Widescreen)
        ->and($block->props->items)->toHaveCount(2)
        ->and($block->props->items[0]->attachment_uuid)->toBeNull();
});

it('carries an optional cover, eyebrow and media slot on a grid item', function () {
    $item = GridItemData::from([
        'title' => 'Fast',
        'pre' => 'New',
        'attachment_uuid' => '9b7c1e2a-4f56-4c3d-8a1b-2e3f4a5b6c7d',
        'media' => ['layout' => 'right', 'max_width' => '32rem', 'ratio' => 'widescreen'],
    ]);

    expect($item->pre)->toBe('New')
        ->and($item->attachment_uuid)->toBe('9b7c1e2a-4f56-4c3d-8a1b-2e3f4a5b6c7d')
        ->and($item->media->layout)->toBe(ItemLayout::Right)
        ->and($item->media->max_width)->toBe('32rem')
        ->and($item->media->ratio)->toBe(MediaRatio::Widescreen);

    // Defaults: no eyebrow/cover, left-aligned, no width cap, ratio inherits from the block.
    $plain = GridItemData::from(['title' => 'Plain']);
    expect($plain->pre)->toBeNull()
        ->and($plain->attachment_uuid)->toBeNull()
        ->and($plain->ctas)->toBe([])
        ->and($plain->media->layout)->toBe(ItemLayout::Left)
        ->and($plain->media->max_width)->toBeNull()
        ->and($plain->media->ratio)->toBe(MediaRatio::Inherit);
});

it('gives a story step the very same shape as a grid item', function () {
    // Same words, same order, `ctas` included — a step used to be denied them.
    // The two classes stay apart so they *can* diverge, not because they do.
    expect(BlockContract::propsProperties(StoryItemData::class))
        ->toBe(BlockContract::propsProperties(GridItemData::class));
});

it('rejects a grid item attachment_uuid longer than 36 chars', function () {
    GridItemData::validate(['title' => 'X', 'attachment_uuid' => str_repeat('a', 37)]);
})->throws(ValidationException::class);

it('rejects an item max_width longer than 32 chars', function () {
    GridItemData::validate([
        'title' => 'X',
        'media' => ['max_width' => str_repeat('a', 33)],
    ]);
})->throws(ValidationException::class);

it('carries an optional media slot on a slide item', function () {
    $slide = SlideItemData::from([
        'title' => 'Cover',
        'attachment_uuid' => '9b7c1e2a-4f56-4c3d-8a1b-2e3f4a5b6c7d',
        'media' => ['layout' => 'before', 'max_width' => '40rem', 'ratio' => 'square'],
    ]);

    expect($slide->media->layout)->toBe(ItemLayout::Before)
        ->and($slide->media->max_width)->toBe('40rem')
        ->and($slide->media->ratio)->toBe(MediaRatio::Square);

    // Defaults: left-aligned, no width cap, ratio inherits from the carousel.
    // `text` is the caption, under the name every other element uses.
    $plain = SlideItemData::from(['text' => 'Text only']);
    expect($plain->text)->toBe('Text only')
        ->and($plain->media->layout)->toBe(ItemLayout::Left)
        ->and($plain->media->max_width)->toBeNull()
        ->and($plain->media->ratio)->toBe(MediaRatio::Inherit);
});

it('round-trips grid item covers through the json props column', function () {
    $page = PublishPage::factory()->create();
    $block = PublishBlock::factory()->forPage($page)->template('grid')->create([
        'props' => ['items' => [
            ['title' => 'Fast', 'attachment_uuid' => '9b7c1e2a-4f56-4c3d-8a1b-2e3f4a5b6c7d', 'media' => ['ratio' => 'square']],
            ['title' => 'Simple'],
        ]],
    ]);

    $block->refresh();

    expect($block->props)->toBeInstanceOf(GridData::class)
        ->and($block->props->items[0]->attachment_uuid)->toBe('9b7c1e2a-4f56-4c3d-8a1b-2e3f4a5b6c7d')
        ->and($block->props->items[0]->media->ratio)->toBe(MediaRatio::Square)
        ->and($block->props->items[1]->attachment_uuid)->toBeNull();
});
