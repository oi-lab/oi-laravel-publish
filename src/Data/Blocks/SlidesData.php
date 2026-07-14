<?php

namespace OiLab\OiLaravelPublish\Data\Blocks;

use OiLab\OiLaravelPublish\Data\CtaData;
use OiLab\OiLaravelPublish\Data\PropsData;
use OiLab\OiLaravelPublish\Data\Styles\SlidesStylesData;
use OiLab\OiLaravelPublish\Enums\MediaRatio;
use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Attributes\Validation\Min;

/**
 * Props for a "slides" (carousel) block. Slide images live in the block's
 * `slides` attachment collection; each `SlideItemData` links the one it shows by
 * `attachment_uuid`, so a slide can be text-only (no attachment) without shifting
 * the others.
 *
 * `media_ratio` is the aspect ratio applied to the whole carousel — the same
 * `MediaRatio` the cover blocks use for `cover_ratio`. Autoplay, interval and
 * loop are behaviour, not presentation; how many slides are visible at a
 * breakpoint is a style (`styles.carousel.per_view`), and where the navigation
 * sits is a style too (`styles.nav_position`).
 */
class SlidesData extends PropsData
{
    /**
     * @param  SlideItemData[]  $items
     * @param  CtaData[]  $ctas
     */
    public function __construct(
        public bool $autoplay = false,
        #[Min(0)]
        public int $interval = 5000,
        public bool $loop = true,
        public MediaRatio $media_ratio = MediaRatio::Inherit,
        #[DataCollectionOf(SlideItemData::class)]
        public array $items = [],
        #[DataCollectionOf(CtaData::class)]
        public array $ctas = [],
        public SlidesStylesData $styles = new SlidesStylesData,
    ) {}
}
