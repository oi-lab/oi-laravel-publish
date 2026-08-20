<?php

use OiLab\OiLaravelPublish\Support\PropsMigration\LotA;
use OiLab\OiLaravelPublish\Support\PropsMigration\PropsMigrator;

/**
 * The props migration, transformation by transformation.
 *
 * Every assertion reads the **raw** column back rather than the hydrated props:
 * PropsCast would answer with the target class either way, which is precisely
 * the reading that cannot tell a migrated block from an unmigrated one.
 */
function runLotA(bool $dryRun = false): array
{
    return (new PropsMigrator)->run(new LotA, $dryRun);
}

it('moves a hero cover layout and ratio into the media slot', function () {
    $block = blockWithRawProps('hero', ['cover_layout' => 'background', 'cover_ratio' => 'square']);

    runLotA();

    expect(rawProps($block))->toBe([
        'styles' => [
            'media' => ['layout' => 'background', 'ratio' => 'square'],
            'block' => ['width' => 'full'],
        ],
    ]);
});

it('drops the cover layout of a block that has no cover of its own', function () {
    $block = blockWithRawProps('grid', ['cover_layout' => 'left', 'cover_ratio' => 'widescreen']);

    runLotA();

    $props = rawProps($block);

    expect($props)->not->toHaveKey('cover_layout')
        ->and($props)->not->toHaveKey('cover_ratio')
        // The ratio survives as the one its items fall back to.
        ->and($props['styles']['media']['ratio'])->toBe('widescreen');
});

it('moves the carousel ratio and navigation into their slots', function () {
    $block = blockWithRawProps('slides', [
        'media_ratio' => 'widescreen',
        'styles' => ['nav_position' => 'top', 'nav_size' => 'large'],
    ]);

    runLotA();

    $props = rawProps($block);

    expect($props)->not->toHaveKey('media_ratio')
        ->and($props['styles'])->not->toHaveKey('nav_position')
        ->and($props['styles'])->not->toHaveKey('nav_size')
        ->and($props['styles']['media']['ratio'])->toBe('widescreen')
        ->and($props['styles']['carousel']['nav_position'])->toBe('top')
        ->and($props['styles']['carousel']['nav_size'])->toBe('large');
});

it('renames the content body slot away from the column it styles', function () {
    $block = blockWithRawProps('content', ['styles' => ['description' => ['align' => 'center']]]);

    runLotA();

    $props = rawProps($block);

    expect($props['styles'])->not->toHaveKey('description')
        ->and($props['styles']['body']['align'])->toBe('center');
});

it('normalises every map provider that is not google', function () {
    $google = blockWithRawProps('map', ['provider' => 'google']);
    $mapbox = blockWithRawProps('map', ['provider' => 'mapbox']);
    $none = blockWithRawProps('map', ['provider' => null]);
    $absent = blockWithRawProps('map', []);

    runLotA();

    expect(rawProps($google)['provider'])->toBe('google')
        ->and(rawProps($mapbox)['provider'])->toBe('openstreetmap')
        ->and(rawProps($none)['provider'])->toBe('openstreetmap')
        // Never added where it was never stored: the DTO default says the same.
        ->and(rawProps($absent))->not->toHaveKey('provider');
});

it('drops the breadcrumb items nothing ever read', function () {
    $block = blockWithRawProps('breadcrumb', ['items' => [['label' => 'Accueil', 'url' => '/']]]);

    runLotA();

    expect(rawProps($block))->not->toHaveKey('items');
});

it('writes the width each block used to hard-code', function () {
    $blocks = [];

    foreach (['hero' => 'full', 'grid' => 'lg', 'content' => 'md', 'form' => 'sm', 'table' => 'full'] as $key => $width) {
        $blocks[$key] = [blockWithRawProps($key, []), $width];
    }

    runLotA();

    foreach ($blocks as $key => [$block, $width]) {
        expect(rawProps($block)['styles']['block']['width'])->toBe($width, "`{$key}` should be {$width}");
    }
});

it('overwrites the width a block was stored with', function () {
    // The only transformation of the lot that overwrites. `width` has never been
    // editable, so a stored value is the DTO default and not a choice — and the
    // default is narrower than what seven templates actually rendered at.
    $block = blockWithRawProps('grid', ['styles' => ['block' => ['width' => 'md']]]);

    runLotA();

    expect(rawProps($block)['styles']['block']['width'])->toBe('lg');
});

it('is idempotent', function () {
    $block = blockWithRawProps('hero', ['cover_layout' => 'left', 'cover_ratio' => 'square']);

    runLotA();
    $once = rawProps($block);

    $second = runLotA();

    expect(rawProps($block))->toBe($once)
        ->and($second['changed'])->toBe(0);
});

it('leaves a block already in the target shape untouched', function () {
    blockWithRawProps('hero', [
        'styles' => [
            'media' => ['layout' => 'right', 'ratio' => 'inherit'],
            'block' => ['width' => 'full'],
        ],
    ]);

    expect(runLotA()['changed'])->toBe(0);
});

it('shrinks nothing: every template keeps the width it used to render at', function () {
    $blocks = [];

    // The seven that wrote `max-w-7xl` in their own component, against a DTO
    // default of `md` (`max-w-5xl`). This is risk C1 of the plan, and this test
    // is what makes forgetting it a failure rather than a silent regression.
    foreach (['grid', 'warranty', 'faqs', 'map', 'marquee', 'reassurance', 'breadcrumb'] as $key) {
        $blocks[$key] = blockWithRawProps($key, ['styles' => ['block' => ['width' => 'md']]]);
    }

    runLotA();

    foreach ($blocks as $key => $block) {
        expect(rawProps($block)['styles']['block']['width'])->toBe('lg', "`{$key}` used to render at max-w-7xl");
    }
});

it('writes nothing on a dry run', function () {
    $block = blockWithRawProps('hero', ['cover_layout' => 'background']);

    $result = runLotA(dryRun: true);

    expect($result['changed'])->toBe(1)
        ->and(rawProps($block))->toBe(['cover_layout' => 'background']);
});

it('reports the paths a block changes at', function () {
    blockWithRawProps('content', ['styles' => ['description' => ['align' => 'right']]]);

    $reported = [];

    (new PropsMigrator)->run(new LotA, true, 200, function ($change) use (&$reported): void {
        $reported = array_keys($change->changes);
    });

    // Reported leaf by leaf: a slot moving whole still names the values it
    // carries, so the diff can be read before it is trusted.
    expect($reported)->toContain('styles.description.align', 'styles.body.align', 'styles.block.width');
});

it('lists its lots and refuses one it does not know', function () {
    $migrator = new PropsMigrator;

    expect(array_keys($migrator->lots()))->toBe(['A', 'B', 'C'])
        ->and($migrator->lot('a'))->toBeInstanceOf(LotA::class)
        ->and($migrator->lot('Z'))->toBeNull();
});

it('is driven by the artisan command, dry by request', function () {
    $block = blockWithRawProps('hero', ['cover_layout' => 'after']);

    $this->artisan('publish:migrate-props', ['--lot' => 'A', '--dry-run' => true])
        ->assertSuccessful();

    expect(rawProps($block))->toBe(['cover_layout' => 'after']);

    $this->artisan('publish:migrate-props', ['--lot' => 'A'])->assertSuccessful();

    expect(rawProps($block)['styles']['media']['layout'])->toBe('after');
});
