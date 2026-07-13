<?php

use OiLab\OiLaravelPublish\Data\Blocks\HeroData;
use OiLab\OiLaravelPublish\Data\PublishTemplateData;
use OiLab\OiLaravelPublish\Enums\PublishTemplateType;
use OiLab\OiLaravelPublish\OiLaravelPublish;
use OiLab\OiLaravelPublish\Support\PublishTemplateRegistry;

it('resolves a template by key', function () {
    $template = OiLaravelPublish::template('hero');

    expect($template)->toBeInstanceOf(PublishTemplateData::class)
        ->and($template->key)->toBe('hero')
        ->and($template->type)->toBe(PublishTemplateType::Block)
        ->and($template->propsClass)->toBe(HeroData::class)
        ->and($template->isBlock())->toBeTrue();
});

it('returns null for an unknown template', function () {
    expect(OiLaravelPublish::template('does-not-exist'))->toBeNull();
});

it('filters templates by type', function () {
    $pages = OiLaravelPublish::pageTemplates();
    $blocks = OiLaravelPublish::blockTemplates();

    expect(array_keys($pages))->toContain('default', 'landing')
        ->and(array_keys($pages))->not->toContain('hero')
        ->and(array_keys($blocks))->toContain('hero', 'features', 'content', 'slides', 'warranty', 'faqs');
});

it('exposes template keys by type', function () {
    $keys = OiLaravelPublish::registry()->keys(PublishTemplateType::Page);

    expect($keys)->toBe(['default', 'landing']);
});

it('can register a template at runtime', function () {
    $registry = OiLaravelPublish::registry();

    expect($registry->has('custom'))->toBeFalse();

    $registry->register(new PublishTemplateData(
        key: 'custom',
        name: 'Custom block',
        type: PublishTemplateType::Block,
    ));

    expect($registry->has('custom'))->toBeTrue()
        ->and($registry->get('custom')->name)->toBe('Custom block');
});

it('hydrates from a raw config array', function () {
    $registry = new PublishTemplateRegistry([
        ['key' => 'a', 'name' => 'A', 'type' => 'page'],
        ['key' => 'b', 'name' => 'B', 'type' => 'block'],
    ]);

    expect($registry->all())->toHaveCount(2)
        ->and($registry->byType(PublishTemplateType::Block))->toHaveKey('b');
});
