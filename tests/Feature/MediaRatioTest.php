<?php

use OiLab\OiLaravelPublish\Data\Blocks\ContentData;
use OiLab\OiLaravelPublish\Data\Blocks\GridData;
use OiLab\OiLaravelPublish\Data\Blocks\HeroData;
use OiLab\OiLaravelPublish\Data\Blocks\SlidesData;
use OiLab\OiLaravelPublish\Enums\MediaRatio;

it('types the media ratio of every block that shows an image with the shared enum', function () {
    expect(HeroData::from([])->styles->media->ratio)->toBe(MediaRatio::Inherit)
        ->and(HeroData::from(['styles' => ['media' => ['ratio' => 'widescreen']]])->styles->media->ratio)
        ->toBe(MediaRatio::Widescreen)
        ->and(ContentData::from(['styles' => ['media' => ['ratio' => 'square']]])->styles->media->ratio)
        ->toBe(MediaRatio::Square)
        ->and(GridData::from(['styles' => ['media' => ['ratio' => 'basis-portrait']]])->styles->media->ratio)
        ->toBe(MediaRatio::BasisPortrait)
        // The carousel's own ratio used to be a root prop of its own name.
        ->and(SlidesData::from(['styles' => ['media' => ['ratio' => 'basis-landscape']]])->styles->media->ratio)
        ->toBe(MediaRatio::BasisLandscape);
});

it('exposes the five media ratios', function () {
    expect(array_map(fn (MediaRatio $r): string => $r->value, MediaRatio::cases()))
        ->toBe(['inherit', 'square', 'widescreen', 'basis-portrait', 'basis-landscape']);
});
