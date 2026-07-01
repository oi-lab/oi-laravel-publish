<?php

namespace OiLab\OiLaravelPublish\Data\Blocks;

use OiLab\OiLaravelPublish\Data\PropsData;
use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Attributes\Validation\Min;

/**
 * Props for a "slides" (carousel) block. Slide images live in the block's
 * `slides` attachment collection; these props hold carousel behaviour and the
 * per-slide captions matched by position.
 *
 * @property array<int, SlideItemData> $items
 */
class SlidesData extends PropsData
{
    /**
     * @param  array<int, SlideItemData>  $items
     */
    public function __construct(
        public bool $autoplay = false,
        #[Min(0)]
        public int $interval = 5000,
        public bool $loop = true,
        #[DataCollectionOf(SlideItemData::class)]
        public array $items = [],
    ) {}
}
