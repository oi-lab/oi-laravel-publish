<?php

use Illuminate\Database\QueryException;
use OiLab\OiLaravelAttachments\Models\File;
use OiLab\OiLaravelPublish\Data\Blocks\WarrantyData;
use OiLab\OiLaravelPublish\Data\Blocks\WarrantyItemData;
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
        ->and($block->props->title)->toBeString()
        ->and($block->props->overview)->toBeString()
        ->and($block->props->items)->toHaveCount(2)
        ->and($block->props->items[0])->toBeInstanceOf(WarrantyItemData::class)
        ->and($block->props->items[0]->label)->toBeString()
        ->and($block->props->items[1]->description)->toBeNull();
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

    expect($data->key)->toBe('hero')
        ->and($data->template_key)->toBe('hero')
        ->and($data->props)->toBeArray()
        ->and($data->toArray()['props'])->toHaveKey('alignment')
        ->and($data->toArray()['props']['alignment'])->toBe('center');
});
