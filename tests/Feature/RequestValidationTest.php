<?php

use Illuminate\Support\Facades\Validator;
use OiLab\OiLaravelPublish\Http\Requests\PublishBlockRequest;
use OiLab\OiLaravelPublish\Http\Requests\PublishPageRequest;
use OiLab\OiLaravelPublish\Models\PublishPage;

it('accepts a valid page payload', function () {
    $validator = Validator::make(
        ['name' => 'Home', 'slug' => 'home', 'template_key' => 'default', 'is_active' => true],
        (new PublishPageRequest)->rules(),
    );

    expect($validator->passes())->toBeTrue();
});

it('rejects an unknown page template key', function () {
    $validator = Validator::make(
        ['name' => 'Home', 'slug' => 'home', 'template_key' => 'nope'],
        (new PublishPageRequest)->rules(),
    );

    expect($validator->fails())->toBeTrue()
        ->and($validator->errors()->has('template_key'))->toBeTrue();
});

it('accepts a valid block payload', function () {
    $page = PublishPage::factory()->create();

    $validator = Validator::make(
        ['publish_page_id' => $page->id, 'template_key' => 'hero', 'name' => 'Hero', 'key' => 'hero'],
        (new PublishBlockRequest)->rules(),
    );

    expect($validator->passes())->toBeTrue();
});

it('rejects a page template used as a block template', function () {
    $page = PublishPage::factory()->create();

    $validator = Validator::make(
        ['publish_page_id' => $page->id, 'template_key' => 'default', 'name' => 'X', 'key' => 'x'],
        (new PublishBlockRequest)->rules(),
    );

    expect($validator->fails())->toBeTrue()
        ->and($validator->errors()->has('template_key'))->toBeTrue();
});

it('rejects a block referencing a missing page', function () {
    $validator = Validator::make(
        ['publish_page_id' => 9999, 'template_key' => 'hero', 'name' => 'Hero', 'key' => 'hero'],
        (new PublishBlockRequest)->rules(),
    );

    expect($validator->fails())->toBeTrue()
        ->and($validator->errors()->has('publish_page_id'))->toBeTrue();
});
