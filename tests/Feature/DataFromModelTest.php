<?php

use OiLab\OiLaravelAttachments\Models\File;
use OiLab\OiLaravelPublish\Data\Blocks\WarrantyData;
use OiLab\OiLaravelPublish\Data\PublishBlockData;
use OiLab\OiLaravelPublish\Data\PublishPageData;
use OiLab\OiLaravelPublish\Models\PublishBlock;
use OiLab\OiLaravelPublish\Models\PublishPage;
use Spatie\LaravelData\Optional;

describe('PublishPageData::fromModel()', function () {
    it('flattens the model typed props into the raw props map', function () {
        $page = PublishPage::factory()->create(['props' => ['foo' => 'bar']]);

        $data = PublishPageData::fromModel($page);

        expect($data->props)->toBe(['foo' => 'bar'])
            ->and($data->id)->toBe($page->id)
            ->and($data->slug)->toBe($page->slug);
    });

    it('lets spatie build the DTO straight from the model without a TypeError', function () {
        $page = PublishPage::factory()->create(['props' => ['foo' => 'bar']]);

        $data = PublishPageData::from($page);

        expect($data)->toBeInstanceOf(PublishPageData::class)
            ->and($data->props)->toBe(['foo' => 'bar']);
    });

    it('declares the model it maps to, for data_replaces_model introspection', function () {
        $parameter = (new ReflectionMethod(PublishPageData::class, 'fromModel'))->getParameters()[0];

        expect($parameter->getType()?->getName())->toBe(PublishPage::class);
    });
});

describe('PublishBlockData::fromModel()', function () {
    it('flattens the model typed props into the raw props map', function () {
        $page = PublishPage::factory()->create();
        $block = PublishBlock::factory()->forPage($page)->hero()->create(['key' => 'hero']);

        $data = PublishBlockData::fromModel($block);

        expect($data->template_key)->toBe('hero')
            ->and($data->props)->toBeArray()
            ->and($data->props)->toHaveKeys(['pre', 'ctas', 'styles'])
            ->and($data->props)->not->toHaveKey('title');
    });

    it('lets spatie build the DTO straight from the model without a TypeError', function () {
        $page = PublishPage::factory()->create();
        $block = PublishBlock::factory()->forPage($page)->hero()->create();

        expect(PublishBlockData::from($block))->toBeInstanceOf(PublishBlockData::class);
    });

    it('declares the model it maps to, for data_replaces_model introspection', function () {
        $parameter = (new ReflectionMethod(PublishBlockData::class, 'fromModel'))->getParameters()[0];

        expect($parameter->getType()?->getName())->toBe(PublishBlock::class);
    });
});

describe('cover on PublishPageData', function () {
    it('omits it from the payload when the relation is not loaded', function () {
        $page = PublishPage::factory()->create();

        $data = PublishPageData::fromModel($page);

        expect($data->cover)->toBeInstanceOf(Optional::class)
            ->and($data->toArray())->not->toHaveKey('cover');
    });

    it('carries the cover attachment once eager loaded', function () {
        $page = PublishPage::factory()->create();
        $file = File::factory()->create();
        $page->attachFile($file, 'cover');

        $data = PublishPageData::fromModel($page->fresh()->load('cover.file'));

        expect($data->cover?->file_id)->toBe($file->id)
            ->and($data->cover?->file?->filename_disk)->toBe($file->filename_disk)
            ->and($data->toArray())->toHaveKey('cover');
    });

    it('carries a null cover when the relation is loaded but empty', function () {
        $page = PublishPage::factory()->create();

        $data = PublishPageData::fromModel($page->load('cover'));

        expect($data->cover)->toBeNull()
            ->and($data->toArray()['cover'])->toBeNull();
    });
});

describe('cover and slides on PublishBlockData', function () {
    it('omits both from the payload when their relation is not loaded', function () {
        $page = PublishPage::factory()->create();
        $block = PublishBlock::factory()->forPage($page)->hero()->create();

        $data = PublishBlockData::fromModel($block);

        expect($data->cover)->toBeInstanceOf(Optional::class)
            ->and($data->slides)->toBeInstanceOf(Optional::class)
            ->and($data->toArray())->not->toHaveKey('cover')
            ->and($data->toArray())->not->toHaveKey('slides');
    });

    it('carries the cover attachment once eager loaded', function () {
        $page = PublishPage::factory()->create();
        $block = PublishBlock::factory()->forPage($page)->hero()->create();
        $file = File::factory()->create();
        $block->attachFile($file, 'cover');

        $data = PublishBlockData::fromModel($block->fresh()->load('cover.file'));

        expect($data->cover?->file_id)->toBe($file->id)
            ->and($data->cover?->file?->filename_disk)->toBe($file->filename_disk)
            ->and($data->toArray())->toHaveKey('cover');
    });

    it('carries a null cover when the relation is loaded but empty', function () {
        $page = PublishPage::factory()->create();
        $block = PublishBlock::factory()->forPage($page)->hero()->create();

        $data = PublishBlockData::fromModel($block->load('cover'));

        expect($data->cover)->toBeNull()
            ->and($data->toArray()['cover'])->toBeNull();
    });

    it('carries the ordered slides collection once eager loaded', function () {
        $page = PublishPage::factory()->create();
        $block = PublishBlock::factory()->forPage($page)->hero()->create();
        $first = File::factory()->create();
        $second = File::factory()->create();
        $block->attachFile($first, 'slides');
        $block->attachFile($second, 'slides');

        $data = PublishBlockData::fromModel($block->fresh()->load('slides.file'));

        expect($data->slides)->toHaveCount(2)
            ->and($data->slides[0]->file_id)->toBe($first->id)
            ->and($data->slides[1]->file_id)->toBe($second->id)
            ->and($data->toArray()['slides'])->toHaveCount(2);
    });

    it('keeps a soft deleted file as a null file on its attachment', function () {
        $page = PublishPage::factory()->create();
        $block = PublishBlock::factory()->forPage($page)->hero()->create();
        $file = File::factory()->create();
        $block->attachFile($file, 'cover');
        $file->delete();

        $data = PublishBlockData::fromModel($block->fresh()->load('cover.file'));

        expect($data->cover)->not->toBeNull()
            ->and($data->cover?->file)->toBeNull();
    });
});

describe('the props union', function () {
    it('lists every typed block props class, WarrantyData included', function () {
        $doc = (new ReflectionMethod(PublishBlockData::class, '__construct'))->getDocComment();

        expect($doc)->toContain('WarrantyData');
    });

    it('accepts a warranty block, whose props class is in the union', function () {
        $page = PublishPage::factory()->create();
        $block = PublishBlock::factory()->forPage($page)->warranty()->create();

        $data = PublishBlockData::fromModel($block);

        expect($block->props)->toBeInstanceOf(WarrantyData::class)
            ->and($data->template_key)->toBe('warranty')
            ->and($data->props)->toHaveKey('items');
    });
});

it('carries the ordered gallery collection once eager loaded', function () {
    $page = PublishPage::factory()->create();
    $block = PublishBlock::factory()->forPage($page)->hero()->create();
    $first = File::factory()->create();
    $second = File::factory()->create();
    $block->attachFile($first, 'gallery');
    $block->attachFile($second, 'gallery');

    $data = PublishBlockData::fromModel($block->fresh()->load('gallery.file'));

    expect($data->gallery)->toHaveCount(2)
        ->and($data->gallery[0]->file_id)->toBe($first->id)
        ->and($data->gallery[1]->file_id)->toBe($second->id)
        ->and($data->toArray()['gallery'])->toHaveCount(2);
});

it('omits gallery from the payload when the relation is not loaded', function () {
    $page = PublishPage::factory()->create();
    $block = PublishBlock::factory()->forPage($page)->hero()->create();

    $data = PublishBlockData::fromModel($block);

    expect($data->gallery)->toBeInstanceOf(Optional::class)
        ->and($data->toArray())->not->toHaveKey('gallery');
});
