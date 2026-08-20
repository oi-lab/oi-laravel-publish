<?php

use OiLab\OiLaravelPublish\Support\PropsMigration\LotC;
use OiLab\OiLaravelPublish\Support\PropsMigration\PropsMigrator;

/**
 * Lot C — the block's single style slot splits into its three areas.
 *
 * Same reading as lots A and B: the raw column, never the hydrated props. A
 * block already at the target shape has to come back untouched, which is what
 * makes replaying the lot safe.
 */
function runLotC(bool $dryRun = false): array
{
    return (new PropsMigrator)->run(new LotC, $dryRun);
}

it('copies the six area fields from styles.block to all three areas, and forgets them there', function () {
    $block = blockWithRawProps('content', ['styles' => ['block' => [
        'height' => 'inherit',
        'width' => 'lg',
        'margin_x' => 'left',
        'margin_y' => 'lg',
        'padding_y' => 'sm',
        'space_y' => 'xl',
        'space_x' => 'sm',
        'items' => 'center',
        'justify' => 'between',
        'theme' => 'dark',
    ]]]);

    runLotC();

    $styles = rawProps($block)['styles'];
    $area = [
        'width' => 'lg',
        'margin_x' => 'left',
        'padding_y' => 'sm',
        'space_y' => 'xl',
        'items' => 'center',
        'justify' => 'between',
    ];

    expect($styles['header_area'])->toBe($area)
        ->and($styles['body_area'])->toBe($area)
        ->and($styles['footer_area'])->toBe($area)
        // `height`, `theme` and `space_x` stay on the block's own section;
        // `margin_y` stays too, unmigrated — it means page rhythm there, not
        // the area's additive margin.
        ->and($styles['block'])->toBe([
            'height' => 'inherit',
            'margin_y' => 'lg',
            'space_x' => 'sm',
            'theme' => 'dark',
        ]);
});

it('leaves out an area field the stored block never carried', function () {
    $block = blockWithRawProps('warranty', ['styles' => ['block' => [
        'width' => 'full',
        'theme' => 'light',
    ]]]);

    runLotC();

    $styles = rawProps($block)['styles'];

    expect($styles['header_area'])->toBe(['width' => 'full'])
        ->and($styles['block'])->toBe(['theme' => 'light']);
});

it('does nothing to a block with no stored styles.block at all', function () {
    $block = blockWithRawProps('table', []);

    $result = runLotC();

    expect(rawProps($block))->toBe([])
        ->and($result['changed'])->toBe(0);
});

it('never touches hero, breadcrumb or reassurance: they keep one unsplit block slot', function () {
    $hero = blockWithRawProps('hero', ['styles' => ['block' => ['width' => 'lg']]]);
    $breadcrumb = blockWithRawProps('breadcrumb', ['styles' => ['block' => ['width' => 'lg']]]);
    $reassurance = blockWithRawProps('reassurance', ['styles' => ['block' => ['width' => 'lg']]]);

    $result = runLotC();

    expect(rawProps($hero)['styles']['block'])->toBe(['width' => 'lg'])
        ->and(rawProps($breadcrumb)['styles']['block'])->toBe(['width' => 'lg'])
        ->and(rawProps($reassurance)['styles']['block'])->toBe(['width' => 'lg'])
        ->and($result['changed'])->toBe(0);
});

it('is idempotent: a block already split is left exactly as it is', function () {
    $already = [
        'block' => ['height' => 'inherit', 'theme' => 'light'],
        'header_area' => ['width' => 'md'],
        'body_area' => ['width' => 'lg'],
        'footer_area' => ['width' => 'sm'],
    ];
    $block = blockWithRawProps('grid', ['styles' => $already]);

    $result = runLotC();

    expect(rawProps($block)['styles'])->toBe($already)
        ->and($result['changed'])->toBe(0);
});

it('lists itself among the migrator\'s lots', function () {
    $migrator = new PropsMigrator;

    expect($migrator->lot('C'))->toBeInstanceOf(LotC::class)
        ->and($migrator->lot('C')->key())->toBe('C');
});
