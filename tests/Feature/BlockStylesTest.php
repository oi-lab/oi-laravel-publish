<?php

use OiLab\OiLaravelPublish\Data\Blocks\BlockquoteData;
use OiLab\OiLaravelPublish\Data\Blocks\FaqsData;
use OiLab\OiLaravelPublish\Data\Blocks\FeaturesData;
use OiLab\OiLaravelPublish\Data\Blocks\HeroData;
use OiLab\OiLaravelPublish\Data\Blocks\SlidesData;
use OiLab\OiLaravelPublish\Data\Styles\HeroStylesData;
use OiLab\OiLaravelPublish\Enums\BlockHeight;
use OiLab\OiLaravelPublish\Enums\BlockTheme;
use OiLab\OiLaravelPublish\Enums\HeadingTag;
use OiLab\OiLaravelPublish\Enums\HorizontalAlign;
use OiLab\OiLaravelPublish\Enums\ListMarker;
use OiLab\OiLaravelPublish\Enums\TextScale;
use OiLab\OiLaravelPublish\Models\PublishBlock;
use OiLab\OiLaravelPublish\Models\PublishPage;
use OiLab\OiLaravelPublish\OiLaravelPublish;

it('gives a block fully populated styles even from an empty props array', function () {
    $hero = HeroData::from([]);

    expect($hero->styles)->toBeInstanceOf(HeroStylesData::class)
        ->and($hero->styles->title->tag)->toBe(HeadingTag::H2)
        ->and($hero->styles->title->align)->toBe(HorizontalAlign::Left)
        ->and($hero->styles->block->height)->toBe(BlockHeight::Inherit)
        ->and($hero->styles->block->theme)->toBe(BlockTheme::Light);
});

it('overrides only the style keys it is given', function () {
    $hero = HeroData::from([
        'styles' => ['title' => ['tag' => 'h1'], 'block' => ['theme' => 'dark']],
    ]);

    expect($hero->styles->title->tag)->toBe(HeadingTag::H1)
        // Untouched sibling keeps its default.
        ->and($hero->styles->title->align)->toBe(HorizontalAlign::Left)
        ->and($hero->styles->block->theme)->toBe(BlockTheme::Dark)
        ->and($hero->styles->block->height)->toBe(BlockHeight::Inherit)
        ->and($hero->styles->ctas->size)->toBe(TextScale::Base);
});

it('carries responsive columns on a list block', function () {
    $features = FeaturesData::from([
        'styles' => ['list' => ['columns' => ['base' => 1, 'md' => 2, 'xl' => 4], 'marker' => 'svg', 'marker_icon' => 'check.svg']],
    ]);

    expect($features->styles->list->columns->base)->toBe(1)
        ->and($features->styles->list->columns->md)->toBe(2)
        ->and($features->styles->list->columns->lg)->toBeNull()
        ->and($features->styles->list->columns->xl)->toBe(4)
        ->and($features->styles->list->marker)->toBe(ListMarker::Svg)
        ->and($features->styles->list->marker_icon)->toBe('check.svg');
});

it('carries a per-breakpoint slide count on a carousel', function () {
    $slides = SlidesData::from(['styles' => ['carousel' => ['per_view' => ['base' => 1, 'lg' => 3]]]]);

    expect($slides->styles->carousel->per_view->base)->toBe(1)
        ->and($slides->styles->carousel->per_view->lg)->toBe(3);
});

it('exposes only the style slots a block can use', function () {
    expect(array_keys(HeroData::from([])->styles->toArray()))
        ->toBe(['block', 'title', 'excerpt', 'ctas'])
        ->and(array_keys(BlockquoteData::from([])->styles->toArray()))
        ->toBe(['block', 'quote', 'ctas'])
        // A FAQ carries no calls to action: no `ctas` slot.
        ->and(array_keys(FaqsData::from([])->styles->toArray()))
        ->toBe(['block', 'title', 'excerpt', 'list']);
});

it('scales a blockquote typographically', function () {
    $quote = BlockquoteData::from(['styles' => ['quote' => ['size' => 'xl']]]);

    expect($quote->styles->quote->size)->toBe(TextScale::Xl);
});

it('round-trips styles through the json props column', function () {
    $page = PublishPage::factory()->create();
    $block = PublishBlock::factory()->forPage($page)->template('features')->create([
        'props' => ['styles' => ['list' => ['columns' => ['base' => 2]]]],
    ]);

    $block->refresh();

    expect($block->props->styles->list->columns->base)->toBe(2)
        ->and(json_decode($block->getRawOriginal('props'), true))
        ->toHaveKey('styles.list.columns.base');
});

it('seeds the template default columns from config', function () {
    $template = OiLaravelPublish::template('features');

    expect($template->props)->toBe(['styles' => ['list' => ['columns' => ['base' => 1, 'md' => 3]]]]);
});
