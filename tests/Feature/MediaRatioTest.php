<?php

use OiLab\OiLaravelPublish\Data\Blocks\ContentData;
use OiLab\OiLaravelPublish\Data\Blocks\FeaturesData;
use OiLab\OiLaravelPublish\Data\Blocks\HeroData;
use OiLab\OiLaravelPublish\Enums\MediaRatio;

it('types cover_ratio across cover blocks with the shared MediaRatio enum', function () {
    expect(HeroData::from([])->cover_ratio)->toBe(MediaRatio::Inherit)
        ->and(HeroData::from(['cover_ratio' => 'widescreen'])->cover_ratio)->toBe(MediaRatio::Widescreen)
        ->and(ContentData::from(['cover_ratio' => 'square'])->cover_ratio)->toBe(MediaRatio::Square)
        ->and(FeaturesData::from(['cover_ratio' => 'basis-portrait'])->cover_ratio)->toBe(MediaRatio::BasisPortrait);
});

it('exposes the five media ratios', function () {
    expect(array_map(fn (MediaRatio $r): string => $r->value, MediaRatio::cases()))
        ->toBe(['inherit', 'square', 'widescreen', 'basis-portrait', 'basis-landscape']);
});
