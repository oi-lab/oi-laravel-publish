<?php

use OiLab\OiLaravelPublish\Data\Blocks\BlockquoteData;
use OiLab\OiLaravelPublish\Data\Blocks\ContentData;
use OiLab\OiLaravelPublish\Data\Blocks\FaqsData;
use OiLab\OiLaravelPublish\Data\Blocks\GridData;
use OiLab\OiLaravelPublish\Data\Blocks\HeroData;
use OiLab\OiLaravelPublish\Data\Blocks\SlidesData;
use OiLab\OiLaravelPublish\Data\Blocks\StoryData;
use OiLab\OiLaravelPublish\Data\Styles\HeroStylesData;
use OiLab\OiLaravelPublish\Data\Styles\MediaStyleData;
use OiLab\OiLaravelPublish\Enums\BlockHeight;
use OiLab\OiLaravelPublish\Enums\BlockMarginX;
use OiLab\OiLaravelPublish\Enums\BlockMarginY;
use OiLab\OiLaravelPublish\Enums\BlockSpaceY;
use OiLab\OiLaravelPublish\Enums\BlockTheme;
use OiLab\OiLaravelPublish\Enums\BlockWidth;
use OiLab\OiLaravelPublish\Enums\CoverLayout;
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

it('leaves every style slot null from an empty props array, ready to inherit', function () {
    $hero = HeroData::from([]);

    expect($hero->styles)->toBeInstanceOf(HeroStylesData::class)
        ->and($hero->styles->title)->toBeNull()
        ->and($hero->styles->block)->toBeNull()
        ->and($hero->styles->ctas)->toBeNull();
});

it('overrides only the style keys it is given, and leaves an untouched slot null', function () {
    $hero = HeroData::from([
        'styles' => ['title' => ['tag' => 'h1'], 'block' => ['theme' => 'dark']],
    ]);

    expect($hero->styles->title->tag)->toBe(HeadingTag::H1)
        // Untouched sibling within the same slot keeps its own default.
        ->and($hero->styles->title->align)->toBe(HorizontalAlign::Left)
        ->and($hero->styles->block->theme)->toBe(BlockTheme::Dark)
        ->and($hero->styles->block->height)->toBe(BlockHeight::Inherit)
        // A slot never mentioned at all stays null — an override is a slot at
        // a time, not a field at a time.
        ->and($hero->styles->ctas)->toBeNull();
});

it('carries responsive columns on a list block', function () {
    $grid = GridData::from([
        'styles' => ['list' => ['columns' => ['base' => 1, 'md' => 2, 'xl' => 4], 'marker' => 'svg', 'marker_icon' => 'check.svg']],
    ]);

    expect($grid->styles->list->columns->base)->toBe(1)
        ->and($grid->styles->list->columns->md)->toBe(2)
        ->and($grid->styles->list->columns->lg)->toBeNull()
        ->and($grid->styles->list->columns->xl)->toBe(4)
        ->and($grid->styles->list->marker)->toBe(ListMarker::Svg)
        ->and($grid->styles->list->marker_icon)->toBe('check.svg');
});

it('carries a per-breakpoint slide count on a carousel', function () {
    $slides = SlidesData::from(['styles' => ['carousel' => ['per_view' => ['base' => 1, 'lg' => 3]]]]);

    expect($slides->styles->carousel->per_view->base)->toBe(1)
        ->and($slides->styles->carousel->per_view->lg)->toBe(3);
});

it('exposes only the style slots a block can use', function () {
    // `hero` carries the three areas too now — `breadcrumb` and `reassurance`
    // are the only templates left on the single, unsplit `block` slot.
    expect(array_keys(HeroData::from([])->styles->toArray()))
        ->toBe(['block', 'header_area', 'pre', 'title', 'excerpt', 'body_area', 'body', 'media', 'ctas', 'footer_area'])
        // The quotation is the body, and it is served by the `quote` slot.
        // Every other template carries three areas instead of one `block`.
        ->and(array_keys(BlockquoteData::from([])->styles->toArray()))
        ->toBe(['block', 'header_area', 'body_area', 'quote', 'ctas', 'footer_area'])
        // A story lays its steps out as a list, like a grid, but on
        // its own class — the two are free to drift apart.
        ->and(array_keys(StoryData::from([])->styles->toArray()))
        ->toBe(['block', 'header_area', 'pre', 'title', 'excerpt', 'body_area', 'body', 'media', 'list', 'ctas', 'footer_area'])
        // An accordion is a stack: a FAQ has no list styling to offer.
        ->and(array_keys(FaqsData::from([])->styles->toArray()))
        ->toBe(['block', 'header_area', 'pre', 'title', 'excerpt', 'body_area', 'ctas', 'footer_area']);
});

it('scales a blockquote typographically', function () {
    $quote = BlockquoteData::from(['styles' => ['quote' => ['size' => 'xl']]]);

    expect($quote->styles->quote->size)->toBe(TextScale::Xl);
});

it('round-trips styles through the json props column', function () {
    $page = PublishPage::factory()->create();
    $block = PublishBlock::factory()->forPage($page)->template('grid')->create([
        'props' => ['styles' => ['list' => ['columns' => ['base' => 2]]]],
    ]);

    $block->refresh();

    expect($block->props->styles->list->columns->base)->toBe(2)
        ->and(json_decode($block->getRawOriginal('props'), true))
        ->toHaveKey('styles.list.columns.base');
});

it('seeds the template default columns from config', function () {
    $template = OiLaravelPublish::template('grid');

    expect($template->props)->toBe(['styles' => ['list' => ['columns' => ['base' => 1, 'md' => 3]]]]);
});

it('hydrates block spacing from snake_case keys', function () {
    // The block's own slot keeps the page rhythm and the gap between areas; the
    // column's cap and placement are an area's business now.
    $hero = HeroData::from(['styles' => [
        'block' => ['margin_y' => 'lg', 'space_y' => 'sm'],
        'header_area' => ['width' => 'full', 'margin_x' => 'none'],
    ]]);

    expect($hero->styles->block->margin_y)->toBe(BlockMarginY::Large)
        ->and($hero->styles->block->space_y)->toBe(BlockSpaceY::Small)
        ->and($hero->styles->header_area->width)->toBe(BlockWidth::Full)
        ->and($hero->styles->header_area->margin_x)->toBe(BlockMarginX::None);
});

it('hydrates carousel navigation from the carousel slot', function () {
    $slides = SlidesData::from(['styles' => ['carousel' => ['nav_position' => 'top', 'nav_size' => 'large']]]);

    expect($slides->styles->carousel->nav_position)->toBe(SlideNavPosition::Top)
        ->and($slides->styles->carousel->nav_size)->toBe(SlideNavSize::Large);
});

it('keeps every presentation choice of a carousel in its styles', function () {
    expect(SlidesData::from(['styles' => ['media' => ['ratio' => 'widescreen']]])->styles->media->ratio)
        ->toBe(MediaRatio::Widescreen)
        // The ratio and the navigation are presentation: they live only in
        // styles now, never beside the props that drive behaviour.
        ->and(array_keys(SlidesData::from([])->toArray()))
        ->toBe(['pre', 'autoplay', 'interval', 'loop', 'items', 'ctas', 'styles'])
        ->and(SlidesData::from(['styles' => ['carousel' => ['nav_position' => 'top']]])->styles->carousel->nav_position)
        ->toBe(SlideNavPosition::Top)
        // Left untouched, a slide's own carousel styling is null — it inherits
        // the theme's, not a class default of its own.
        ->and(SlidesData::from([])->styles->carousel)->toBeNull();
});

it('draws a block’s media full width until an author says otherwise', function () {
    // The figure used to be full-bleed and nothing else: a video pasted into a
    // content block spanned the section while the text beside it stopped at
    // `md`. The defaults have to reproduce that, or every published block moves.
    $media = ContentData::from([])->styles->media ?? new MediaStyleData;

    expect($media->width)->toBe(BlockWidth::Full)
        ->and($media->margin_x)->toBe(BlockMarginX::Auto)
        ->and($media->margin_y)->toBe(BlockMarginY::None);
});

it('hydrates the media column from snake_case keys', function () {
    $content = ContentData::from(['styles' => ['media' => [
        'layout' => 'before', 'ratio' => 'widescreen',
        'width' => 'md', 'margin_x' => 'left', 'margin_y' => 'lg',
    ]]]);

    expect($content->styles->media->layout)->toBe(CoverLayout::Before)
        ->and($content->styles->media->ratio)->toBe(MediaRatio::Widescreen)
        ->and($content->styles->media->width)->toBe(BlockWidth::Medium)
        ->and($content->styles->media->margin_x)->toBe(BlockMarginX::Left)
        ->and($content->styles->media->margin_y)->toBe(BlockMarginY::Large);
});

it('hydrates a per-slide attachment_uuid through the items collection', function () {
    $slides = SlidesData::from(['items' => [
        ['title' => 'With image', 'attachment_uuid' => '9b7c1e2a-4f56-4c3d-8a1b-2e3f4a5b6c7d'],
        ['title' => 'Text only'],
    ]]);

    expect($slides->items[0]->attachment_uuid)->toBe('9b7c1e2a-4f56-4c3d-8a1b-2e3f4a5b6c7d')
        ->and($slides->items[1]->attachment_uuid)->toBeNull();
});
