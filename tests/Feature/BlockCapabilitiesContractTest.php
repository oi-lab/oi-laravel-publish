<?php

use OiLab\OiLaravelPublish\Data\PublishTemplateData;
use OiLab\OiLaravelPublish\Enums\PublishTemplateType;
use OiLab\OiLaravelPublish\OiLaravelPublish;
use OiLab\OiLaravelPublish\Support\BlockContract;

/**
 * The capabilities matrix, held to the code it describes.
 *
 * `BlockCapabilitiesData` is the single declaration of what a block knows how to
 * do; {@see BlockContract} is what stops that declaration from drifting away
 * from the classes behind it, and this file is what makes it fail the build.
 *
 * The checks themselves live in `src/` on purpose: a host application that
 * registers block templates of its own is held to the same contract by a test
 * of three lines, rather than by a copy of this one.
 */

/**
 * The keys of every block template the package ships, as a Pest dataset.
 *
 * Read straight from the config file rather than from the registry: a dataset is
 * resolved while the suite is being collected, before the application boots, so
 * the registry is still empty at that point. The tests below then resolve each
 * key through the registry, and so assert against the live template.
 *
 * @return array<string, array{string}>
 */
function blockTemplateKeyDataset(): array
{
    /** @var array{templates: array<int, array{key: string, type: string}>} $config */
    $config = require __DIR__.'/../../config/oi-laravel-publish.php';

    $dataset = [];

    foreach ($config['templates'] as $template) {
        if ($template['type'] === PublishTemplateType::Block->value) {
            $dataset[$template['key']] = [$template['key']];
        }
    }

    return $dataset;
}

/**
 * The registered template a dataset key names.
 */
function blockTemplate(string $key): PublishTemplateData
{
    $template = OiLaravelPublish::template($key);

    return $template instanceof PublishTemplateData
        ? $template
        : throw new RuntimeException("The `{$key}` block template is declared in config but not registered.");
}

it('holds every block template to its declared capabilities', function (string $key) {
    $violations = BlockContract::violations(blockTemplate($key));

    expect($violations)->toBe([], implode(PHP_EOL, $violations));
})->with(blockTemplateKeyDataset());

it('never requires a name from a block that does not render one', function (string $key) {
    $template = blockTemplate($key);

    if ($template->capabilities->title) {
        // A block that renders its name stays free to make it optional: a
        // `content` block carries everything it shows in its body.
        expect(true)->toBeTrue();

        return;
    }

    expect($template->requiresName())->toBeFalse(
        "`{$template->key}` does not render a title, so it cannot require a name.",
    );
})->with(blockTemplateKeyDataset());

it('gives a block without a props class no capabilities at all', function (string $key) {
    $template = blockTemplate($key);

    if ($template->propsClass !== null) {
        expect(true)->toBeTrue();

        return;
    }

    $capabilities = $template->capabilities;

    expect($capabilities->pre)->toBeFalse()
        ->and($capabilities->title)->toBeFalse()
        ->and($capabilities->excerpt)->toBeFalse()
        ->and($capabilities->body)->toBeFalse()
        ->and($capabilities->media)->toBe([])
        ->and($capabilities->hasItems())->toBeFalse()
        ->and($capabilities->ctas)->toBeFalse();
})->with(blockTemplateKeyDataset());

it('claims exactly the exceptions it can name', function () {
    // An exception is a decision with a reason written beside it. Adding one
    // silently is what this asserts against — the list is short, and it is meant
    // to be read whole when it changes.
    expect(array_keys(BlockContract::EXCEPTIONS))->toBe([
        'faqs.layout-slot',
        'map.layout-slot',
        'blockquote.body',
        'blockquote.quote',
        'table.table',
        'reassurance.list',
    ]);
});

it('holds every element class to the closed vocabulary', function (string $key) {
    // The check itself lives in BlockContract and runs inside the sweep above;
    // this is what makes the vocabulary readable as a rule of its own, and names
    // the words it replaced.
    $itemsClass = blockTemplate($key)->capabilities->itemsClass;

    if ($itemsClass === null) {
        expect(true)->toBeTrue();

        return;
    }

    $properties = BlockContract::propsProperties($itemsClass);

    foreach ($properties as $property) {
        expect(BlockContract::BANNED_ITEM_PROPERTIES)->not->toHaveKey(
            $property,
            "`{$key}` still names an element property `{$property}`.",
        );
    }
})->with(blockTemplateKeyDataset());

it('reads a block\'s style slots off the class that types them', function () {
    expect(BlockContract::styleSlots(OiLaravelPublish::template('hero')?->propsClass))
        ->toBe(['block', 'header_area', 'pre', 'title', 'excerpt', 'body_area', 'body', 'media', 'ctas', 'footer_area'])
        // A block with no props class declares no slots, and no capabilities.
        ->and(BlockContract::styleSlots(null))->toBe([]);
});
