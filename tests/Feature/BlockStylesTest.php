<?php

use OiLab\OiLaravelPublish\Data\Blocks\BlockquoteData;
use OiLab\OiLaravelPublish\Data\Blocks\FaqsData;
use OiLab\OiLaravelPublish\Data\Blocks\FeaturesData;
use OiLab\OiLaravelPublish\Data\Blocks\HeroData;
use OiLab\OiLaravelPublish\Data\Blocks\SlidesData;
use OiLab\OiLaravelPublish\Data\Blocks\StoryData;
use OiLab\OiLaravelPublish\Data\Styles\HeroStylesData;
use OiLab\OiLaravelPublish\Enums\BlockHeight;
use OiLab\OiLaravelPublish\Enums\BlockMarginX;
use OiLab\OiLaravelPublish\Enums\BlockMarginY;
use OiLab\OiLaravelPublish\Enums\BlockSpaceY;
use OiLab\OiLaravelPublish\Enums\BlockTheme;
use OiLab\OiLaravelPublish\Enums\BlockWidth;
use OiLab\OiLaravelPublish\Enums\HeadingTag;
use OiLab\OiLaravelPublish\Enums\HorizontalAlign;
use OiLab\OiLaravelPublish\Enums\ListMarker;
use OiLab\OiLaravelPublish\Enums\MediaRatio;
use OiLab\OiLaravelPublish\Enums\SlideNavPosition;
use OiLab\OiLaravelPublish\Enums\SlideNavSize;
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
        ->toBe(['block', 'pre', 'title', 'excerpt', 'ctas'])
        ->and(array_keys(BlockquoteData::from([])->styles->toArray()))
        ->toBe(['block', 'quote', 'ctas'])
        // A story lays its steps out as a list, like a features grid, but on
        // its own class — the two are free to drift apart.
        ->and(array_keys(StoryData::from([])->styles->toArray()))
        ->toBe(['block', 'title', 'excerpt', 'ctas', 'list'])
        // A FAQ carries no calls to action and no list styling.
        ->and(array_keys(FaqsData::from([])->styles->toArray()))
        ->toBe(['block', 'title', 'excerpt']);
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

it('hydrates block spacing from snake_case keys', function () {
    $hero = HeroData::from(['styles' => ['block' => [
        'width' => 'full', 'margin_x' => 'none', 'margin_y' => 'lg', 'space_y' => 'sm',
    ]]]);

    expect($hero->styles->block->width)->toBe(BlockWidth::Full)
        ->and($hero->styles->block->margin_x)->toBe(BlockMarginX::None)
        ->and($hero->styles->block->margin_y)->toBe(BlockMarginY::Large)
        ->and($hero->styles->block->space_y)->toBe(BlockSpaceY::Small);
});

it('hydrates carousel navigation from snake_case keys', function () {
    $slides = SlidesData::from(['styles' => ['nav_position' => 'top', 'nav_size' => 'large']]);

    expect($slides->styles->nav_position)->toBe(SlideNavPosition::Top)
        ->and($slides->styles->nav_size)->toBe(SlideNavSize::Large);
});

it('carries a block-level media ratio and no duplicated nav position prop', function () {
    expect(SlidesData::from([])->media_ratio)->toBe(MediaRatio::Inherit)
        ->and(SlidesData::from(['media_ratio' => 'widescreen'])->media_ratio)->toBe(MediaRatio::Widescreen)
        // navPosition is presentation: it lives only in styles now, not in props.
        ->and(array_keys(SlidesData::from([])->toArray()))->not->toContain('navPosition')
        ->and(SlidesData::from([])->styles->nav_position)->toBe(SlideNavPosition::Bottom);
});

it('hydrates a per-slide attachment_uuid through the items collection', function () {
    $slides = SlidesData::from(['items' => [
        ['title' => 'With image', 'attachment_uuid' => '9b7c1e2a-4f56-4c3d-8a1b-2e3f4a5b6c7d'],
        ['title' => 'Text only'],
    ]]);

    expect($slides->items[0]->attachment_uuid)->toBe('9b7c1e2a-4f56-4c3d-8a1b-2e3f4a5b6c7d')
        ->and($slides->items[1]->attachment_uuid)->toBeNull();
});
