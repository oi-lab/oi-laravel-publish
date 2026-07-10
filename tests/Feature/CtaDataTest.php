<?php

use Illuminate\Validation\ValidationException;
use OiLab\OiLaravelPublish\Data\Blocks\HeroData;
use OiLab\OiLaravelPublish\Data\Blocks\SlideItemData;
use OiLab\OiLaravelPublish\Data\CtaData;
use OiLab\OiLaravelPublish\Enums\CtaPosition;
use OiLab\OiLaravelPublish\Enums\CtaSize;
use OiLab\OiLaravelPublish\Enums\CtaTarget;
use OiLab\OiLaravelPublish\Enums\CtaVariant;

it('defaults every presentation axis, leaving position to the component', function () {
    $cta = CtaData::from(['label' => 'Buy', 'url' => '/buy']);

    expect($cta->target)->toBe(CtaTarget::Self)
        ->and($cta->variant)->toBe(CtaVariant::Default)
        ->and($cta->size)->toBe(CtaSize::Default)
        ->and($cta->position)->toBeNull();
});

it('serialises its enums to their backed values', function () {
    $cta = new CtaData(
        label: 'Buy',
        url: '/buy',
        target: CtaTarget::Blank,
        variant: CtaVariant::Ghost,
        size: CtaSize::Xs,
        position: CtaPosition::Footer,
    );

    expect($cta->toArray())->toBe([
        'label' => 'Buy',
        'url' => '/buy',
        'target' => '_blank',
        'variant' => 'ghost',
        'size' => 'xs',
        'position' => 'footer',
    ]);
});

it('hydrates a block ctas collection from a raw array', function () {
    $hero = HeroData::from([
        'ctas' => [
            ['label' => 'Primary', 'url' => '/a', 'position' => 'header'],
            ['label' => 'Secondary', 'url' => '/b', 'variant' => 'link', 'size' => 'sm'],
        ],
    ]);

    expect($hero->ctas)->toHaveCount(2)
        ->and($hero->ctas[0])->toBeInstanceOf(CtaData::class)
        ->and($hero->ctas[0]->position)->toBe(CtaPosition::Header)
        ->and($hero->ctas[1]->variant)->toBe(CtaVariant::Link)
        ->and($hero->ctas[1]->size)->toBe(CtaSize::Sm)
        ->and($hero->ctas[1]->position)->toBeNull();
});

it('gives a block an empty ctas collection by default', function () {
    expect(HeroData::from([])->ctas)->toBe([]);
});

it('carries a single, unpositioned cta on a slide', function () {
    $slide = SlideItemData::from([
        'title' => 'One',
        'cta' => ['label' => 'See', 'url' => '/see'],
    ]);

    expect($slide->cta)->toBeInstanceOf(CtaData::class)
        ->and($slide->cta->label)->toBe('See')
        ->and($slide->cta->position)->toBeNull()
        ->and(SlideItemData::from(['title' => 'Two'])->cta)->toBeNull();
});

it('rejects a cta without a label or a url', function () {
    CtaData::validate(['url' => '/no-label']);
})->throws(ValidationException::class);
