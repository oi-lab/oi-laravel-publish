<?php

use Illuminate\Database\QueryException;
use OiLab\OiLaravelAttachments\Models\File;
use OiLab\OiLaravelPublish\Data\PublishTemplateData;
use OiLab\OiLaravelPublish\Models\PublishBlock;
use OiLab\OiLaravelPublish\Models\PublishPage;

it('creates a page with a generated uuid', function () {
    $page = PublishPage::factory()->create();

    expect($page->uuid)->not->toBeNull()
        ->and(PublishPage::count())->toBe(1)
        ->and($page->is_active)->toBeTrue();
});

it('nests pages recursively through parent and children', function () {
    $root = PublishPage::factory()->create();
    $childB = PublishPage::factory()->childOf($root)->create(['sort' => 2]);
    $childA = PublishPage::factory()->childOf($root)->create(['sort' => 1]);

    expect($childA->parent->is($root))->toBeTrue()
        ->and($root->children)->toHaveCount(2)
        ->and($root->children->pluck('id')->all())->toBe([$childA->id, $childB->id]);
});

it('owns an ordered collection of blocks', function () {
    $page = PublishPage::factory()->create();
    PublishBlock::factory()->forPage($page)->create(['sort' => 2, 'key' => 'second']);
    PublishBlock::factory()->forPage($page)->create(['sort' => 1, 'key' => 'first']);

    expect($page->blocks)->toHaveCount(2)
        ->and($page->blocks->pluck('key')->all())->toBe(['first', 'second']);
});

it('allows the same slug under different parents', function () {
    $a = PublishPage::factory()->create();
    $b = PublishPage::factory()->create();

    PublishPage::factory()->childOf($a)->create(['slug' => 'about']);
    PublishPage::factory()->childOf($b)->create(['slug' => 'about']);

    expect(PublishPage::where('slug', 'about')->count())->toBe(2);
});

it('rejects a duplicate slug under the same parent', function () {
    $parent = PublishPage::factory()->create();
    PublishPage::factory()->childOf($parent)->create(['slug' => 'team']);

    PublishPage::factory()->childOf($parent)->create(['slug' => 'team']);
})->throws(QueryException::class);

it('resolves its template from the registry', function () {
    $page = PublishPage::factory()->landing()->create();

    expect($page->template())->toBeInstanceOf(PublishTemplateData::class)
        ->and($page->template()->key)->toBe('landing');
});

it('attaches a single cover image', function () {
    $page = PublishPage::factory()->create();
    $file = File::factory()->create();

    $page->attachFile($file, 'cover');

    expect($page->cover)->not->toBeNull()
        ->and($page->cover->file->is($file))->toBeTrue();
});

it('exposes flat props in its data object', function () {
    $page = PublishPage::factory()->create(['props' => ['foo' => 'bar']]);

    $data = $page->toData();

    expect($data->props)->toBe(['foo' => 'bar'])
        ->and($data->toArray()['props'])->toBe(['foo' => 'bar']);
});

it('soft deletes a page', function () {
    $page = PublishPage::factory()->create();

    $page->delete();

    expect(PublishPage::count())->toBe(0)
        ->and(PublishPage::withTrashed()->count())->toBe(1);
});

it('cascades block deletion when a page is force deleted', function () {
    $page = PublishPage::factory()->create();
    PublishBlock::factory()->forPage($page)->create();

    $page->forceDelete();

    expect(PublishBlock::count())->toBe(0);
});
