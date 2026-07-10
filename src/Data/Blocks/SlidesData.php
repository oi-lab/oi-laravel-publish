<?php

namespace OiLab\OiLaravelPublish\Data\Blocks;

use OiLab\OiLaravelPublish\Data\CtaData;
use OiLab\OiLaravelPublish\Data\PropsData;
use OiLab\OiLaravelPublish\Data\Styles\SlidesStylesData;
use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Attributes\Validation\Min;

/**
 * Props for a "slides" (carousel) block. Slide images live in the block's
 * `slides` attachment collection; these props hold carousel behaviour and the
 * per-slide captions matched by position.
 *
 * Autoplay, interval and loop are behaviour, not presentation — how many slides
 * are visible at a breakpoint is a style, see `styles.carousel.per_view`.
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
        #[DataCollectionOf(SlideItemData::class)]
        public array $items = [],
        #[DataCollectionOf(CtaData::class)]
        public array $ctas = [],
        public SlidesStylesData $styles = new SlidesStylesData,
    ) {}
}
