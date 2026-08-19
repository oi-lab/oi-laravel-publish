<?php

use OiLab\OiLaravelPublish\Support\PropsMigration\LotB;
use OiLab\OiLaravelPublish\Support\PropsMigration\PropsMigrator;

/**
 * Lot B, transformation by transformation.
 *
 * Same reading as lot A: the raw column, never the hydrated props. A rename that
 * did not happen is invisible through `PropsCast` — the property is simply
 * absent, and spatie fills it with its default without a word. That is the whole
 * risk of this phase, and it is what these assertions are aimed at.
 */
function runLotB(bool $dryRun = false): array
{
    return (new PropsMigrator)->run(new LotB, $dryRun);
}

it('folds the three flat media fields of an element into its media slot', function () {
    $block = blockWithRawProps('grid', ['items' => [
        ['title' => 'Fast', 'item_layout' => 'right', 'cover_ratio' => 'square', 'max_width' => '32rem'],
        ['title' => 'Plain'],
    ]]);

    runLotB();

    $items = rawProps($block)['items'];

    expect($items[0])->not->toHaveKeys(['item_layout', 'cover_ratio', 'max_width'])
        ->and($items[0]['media'])->toBe([
            'layout' => 'right',
            'ratio' => 'square',
            'max_width' => '32rem',
        ])
        // An element that carried none of the three gains nothing: its media
        // slot is the DTO's default either way.
        ->and($items[1])->toBe(['title' => 'Plain']);
});

it('renames a slide caption to the word every element uses', function () {
    $block = blockWithRawProps('slides', ['items' => [
        ['title' => 'One', 'caption' => 'Une légende **markdown**'],
    ]]);

    runLotB();

    $item = rawProps($block)['items'][0];

    expect($item)->not->toHaveKey('caption')
        ->and($item['text'])->toBe('Une légende **markdown**');
});

it('turns the single cta of a slide into a collection, unpositioned', function () {
    $block = blockWithRawProps('slides', ['items' => [
        ['title' => 'One', 'cta' => ['label' => 'Voir', 'url' => '/voir']],
        ['title' => 'Two', 'cta' => null],
    ]]);

    runLotB();

    $items = rawProps($block)['items'];

    expect($items[0])->not->toHaveKey('cta')
        ->and($items[0]['ctas'])->toHaveCount(1)
        ->and($items[0]['ctas'][0]['label'])->toBe('Voir')
        // A slide has no slot to place an action in, so it keeps falling in
        // after the text, exactly where it fell before.
        ->and($items[0]['ctas'][0]['position'])->toBeNull()
        ->and($items[1]['ctas'])->toBe([]);
});

it('renames a question and an answer', function () {
    $block = blockWithRawProps('faqs', ['items' => [
        ['question' => 'Pourquoi ?', 'answer' => 'Parce que.'],
    ]]);

    runLotB();

    $item = rawProps($block)['items'][0];

    expect($item)->not->toHaveKeys(['question', 'answer'])
        ->and($item['title'])->toBe('Pourquoi ?')
        ->and($item['text'])->toBe('Parce que.');
});

it('renames a marker label and description, under the markers key', function () {
    $block = blockWithRawProps('map', ['markers' => [
        ['label' => 'Siège', 'description' => 'Ouvert du lundi au vendredi.', 'latitude' => 45.76],
    ]]);

    runLotB();

    $marker = rawProps($block)['markers'][0];

    expect($marker)->not->toHaveKeys(['label', 'description'])
        ->and($marker['title'])->toBe('Siège')
        ->and($marker['text'])->toBe('Ouvert du lundi au vendredi.')
        // The domain fields are untouched: they were never part of the rename.
        ->and($marker['latitude'])->toBe(45.76);
});

it('leaves the elements of a block it does not know alone', function () {
    $block = blockWithRawProps('table', ['headers' => ['A'], 'rows' => [['1']]]);

    expect(runLotB()['changed'])->toBe(0)
        ->and(rawProps($block))->toBe(['headers' => ['A'], 'rows' => [['1']]]);
});

it('is idempotent', function () {
    $block = blockWithRawProps('slides', ['items' => [
        ['title' => 'One', 'caption' => 'Texte', 'cover_ratio' => 'square', 'cta' => ['label' => 'Voir', 'url' => '/v']],
    ]]);

    runLotB();
    $once = rawProps($block);

    $second = runLotB();

    expect(rawProps($block))->toBe($once)
        ->and($second['changed'])->toBe(0);
});

it('writes nothing on a dry run', function () {
    $block = blockWithRawProps('faqs', ['items' => [['question' => 'Q', 'answer' => 'A']]]);

    $result = runLotB(dryRun: true);

    expect($result['changed'])->toBe(1)
        ->and(rawProps($block)['items'][0])->toBe(['question' => 'Q', 'answer' => 'A']);
});

it('never touches what lot A owns', function () {
    // The two lots do not overlap, which is what lets them be replayed
    // independently and in any order.
    $block = blockWithRawProps('slides', [
        'media_ratio' => 'widescreen',
        'styles' => ['nav_position' => 'top'],
        'items' => [['title' => 'One', 'caption' => 'Texte']],
    ]);

    runLotB();

    $props = rawProps($block);

    expect($props['media_ratio'])->toBe('widescreen')
        ->and($props['styles']['nav_position'])->toBe('top')
        ->and($props['items'][0]['text'])->toBe('Texte');
});
