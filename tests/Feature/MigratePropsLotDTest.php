<?php

use OiLab\OiLaravelPublish\Data\Blocks\HeroData;
use OiLab\OiLaravelPublish\Support\PropsMigration\LotD;

/**
 * Lot D — the `hero`'s one style slot becomes three areas.
 *
 * `hero` sat out {@see LotC}: it kept width, margin_x, padding_y, space_y, items
 * and justify on a single `styles.block` worn by its whole column. Those six now
 * belong to a header, a body and a footer that can differ, and a published hero
 * must come through the move looking exactly as it did — which means the stored
 * value is copied to all three areas, not dropped and re-defaulted.
 */
$lot = new LotD;

it('copies the block\'s column settings to each of the three areas', function () use ($lot) {
    $props = ['styles' => ['block' => [
        'width' => 'md',
        'margin_x' => 'auto',
        'padding_y' => 'lg',
        'space_y' => 'sm',
        'items' => 'center',
        'justify' => 'between',
        // The page rhythm and the colour scheme stay on the block itself.
        'margin_y' => 'xl',
        'theme' => 'dark',
    ]]];

    $after = $lot->migrate('hero', $props);
    $area = [
        'width' => 'md', 'margin_x' => 'auto', 'padding_y' => 'lg',
        'space_y' => 'sm', 'items' => 'center', 'justify' => 'between',
    ];

    expect($after['styles']['header_area'])->toBe($area)
        ->and($after['styles']['body_area'])->toBe($area)
        ->and($after['styles']['footer_area'])->toBe($area)
        // One shared column is what the hero rendered, so three identical areas
        // are what reproduce it.
        ->and($after['styles']['block'])->toBe(['margin_y' => 'xl', 'theme' => 'dark']);
});

it('carries over only the fields the block actually stored', function () use ($lot) {
    $after = $lot->migrate('hero', ['styles' => ['block' => ['width' => 'full']]]);

    expect($after['styles']['header_area'])->toBe(['width' => 'full'])
        // The rest is filled by BlockAreaStyleData's own defaults at read time,
        // not written out here.
        ->and($after['styles']['block'])->toBe([]);
});

it('leaves a hero with no stored styles alone', function () use ($lot) {
    expect($lot->migrate('hero', ['pre' => 'Bienvenue']))->toBe(['pre' => 'Bienvenue'])
        ->and($lot->migrate('hero', []))->toBe([]);
});

it('is idempotent: a hero already split is handed back untouched', function () use ($lot) {
    $split = ['styles' => [
        'block' => ['margin_y' => 'lg'],
        'header_area' => ['width' => 'sm'],
        'body_area' => ['width' => 'md'],
        'footer_area' => ['width' => 'lg'],
    ]];

    // Replaying must not overwrite an area an author has since set apart.
    expect($lot->migrate('hero', $split))->toBe($split);
});

it('touches no other template', function () use ($lot) {
    $props = ['styles' => ['block' => ['width' => 'md']]];

    foreach (['content', 'grid', 'breadcrumb', 'reassurance', 'story'] as $key) {
        expect($lot->migrate($key, $props))->toBe($props);
    }
});

it('hydrates the migrated shape into the three areas of HeroStylesData', function () use ($lot) {
    $after = $lot->migrate('hero', ['styles' => ['block' => [
        'width' => 'lg', 'margin_x' => 'left', 'margin_y' => 'xl',
    ]]]);

    $hero = HeroData::from($after);

    expect($hero->styles->header_area->width->value)->toBe('lg')
        ->and($hero->styles->body_area->margin_x->value)->toBe('left')
        ->and($hero->styles->footer_area->width->value)->toBe('lg')
        ->and($hero->styles->block->margin_y->value)->toBe('xl');
});
