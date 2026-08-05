<?php

use Illuminate\Support\Facades\Validator;
use OiLab\OiLaravelPublish\Data\Pages\PagePropsData;
use OiLab\OiLaravelPublish\Data\ParamData;
use OiLab\OiLaravelPublish\Data\PublishPageData;
use OiLab\OiLaravelPublish\Http\Requests\PublishPageRequest;
use OiLab\OiLaravelPublish\Models\PublishPage;

it('casts page props to the typed class declared by the page templates', function (string $template) {
    $page = PublishPage::factory()->create(['template_key' => $template]);
    $page->refresh();

    expect($page->props)->toBeInstanceOf(PagePropsData::class)
        ->and($page->props->params)->toBe([]);
})->with(['default', 'landing']);

it('hydrates the params into typed ParamData objects', function () {
    $page = PublishPage::factory()->create([
        'props' => ['params' => [
            ['key' => 'gtm_id', 'value' => 'GTM-1234'],
            ['key' => 'variant', 'value' => null],
        ]],
    ]);
    $page->refresh();

    expect($page->props->params)->toHaveCount(2)
        ->and($page->props->params[0])->toBeInstanceOf(ParamData::class)
        ->and($page->props->params[0]->key)->toBe('gtm_id')
        ->and($page->props->params[0]->value)->toBe('GTM-1234')
        ->and($page->props->params[1]->value)->toBeNull();
});

it('reads a param by key, falling back to the default', function () {
    $page = PublishPage::factory()->withParams(['gtm_id' => 'GTM-1234', 'variant' => null])->create();
    $page->refresh();

    expect($page->props->param('gtm_id'))->toBe('GTM-1234')
        ->and($page->props->param('missing'))->toBeNull()
        ->and($page->props->param('missing', 'fallback'))->toBe('fallback')
        // A param holding null reads as the default; hasParam() tells them apart.
        ->and($page->props->param('variant', 'fallback'))->toBe('fallback')
        ->and($page->props->hasParam('variant'))->toBeTrue()
        ->and($page->props->hasParam('missing'))->toBeFalse();
});

it('exposes the params as a map, last occurrence winning', function () {
    $props = PagePropsData::from(['params' => [
        ['key' => 'theme', 'value' => 'light'],
        ['key' => 'theme', 'value' => 'dark'],
    ]]);

    expect($props->paramsMap())->toBe(['theme' => 'dark']);
});

it('reads a param straight off the model', function () {
    $page = PublishPage::factory()->withParams(['gtm_id' => 'GTM-1234'])->create();
    $page->refresh();

    expect($page->param('gtm_id'))->toBe('GTM-1234')
        ->and($page->param('missing', 'fallback'))->toBe('fallback')
        ->and($page->params())->toBe(['gtm_id' => 'GTM-1234']);
});

it('reads params off a page whose template declares no typed props class', function () {
    typelessPageTemplate('typeless-params');

    $page = PublishPage::factory()->create([
        'template_key' => 'typeless-params',
        'props' => ['params' => [['key' => 'gtm_id', 'value' => 'GTM-1234']]],
    ]);
    $page->refresh();

    expect($page->param('gtm_id'))->toBe('GTM-1234');
});

it('round-trips the params through the json column', function () {
    $page = PublishPage::factory()->create([
        'props' => new PagePropsData([new ParamData('gtm_id', 'GTM-1234')]),
    ]);

    expect(json_decode($page->getRawOriginal('props'), true))
        ->toBe(['params' => [['key' => 'gtm_id', 'value' => 'GTM-1234']]]);

    $page->refresh();
    expect($page->props->param('gtm_id'))->toBe('GTM-1234');
});

it('carries the flattened params in the page DTO', function () {
    $page = PublishPage::factory()->withParams(['gtm_id' => 'GTM-1234'])->create();
    $page->refresh();

    expect(PublishPageData::fromModel($page)->props)
        ->toBe(['params' => [['key' => 'gtm_id', 'value' => 'GTM-1234']]]);
});

it('validates the params of a page payload', function () {
    $rules = (new PublishPageRequest)->rules();
    $payload = ['name' => 'Home', 'slug' => 'home', 'template_key' => 'default'];

    $valid = Validator::make(
        [...$payload, 'props' => ['params' => [['key' => 'gtm_id', 'value' => 'GTM-1234'], ['key' => 'variant']]]],
        $rules,
    );

    $invalid = Validator::make(
        [...$payload, 'props' => ['params' => [['value' => 'orphan']]]],
        $rules,
    );

    expect($valid->passes())->toBeTrue()
        ->and($invalid->fails())->toBeTrue()
        ->and($invalid->errors()->has('props.params.0.key'))->toBeTrue();
});
