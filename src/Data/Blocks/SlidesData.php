<?php

namespace OiLab\OiLaravelPublish\Data\Blocks;

use OiLab\OiLaravelPublish\Data\CtaData;
use OiLab\OiLaravelPublish\Data\Items\SlideItemData;
use OiLab\OiLaravelPublish\Data\PropsData;
use OiLab\OiLaravelPublish\Data\Styles\SlidesStylesData;
use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Attributes\Validation\Max;
use Spatie\LaravelData\Attributes\Validation\Min;
use Spatie\LaravelData\Attributes\Validation\Nullable;

/**
 * Props for a "slides" (carousel) block. Slide images live in the block's
 * `slides` attachment collection; each `SlideItemData` links the one it shows by
 * `attachment_uuid`, so a slide can be text-only (no attachment) without shifting
 * the others.
 *
 * Autoplay, interval and loop are behaviour, and stay here. Everything about the
 * carousel's appearance is a style: the ratio the whole strip falls back to
 * (`styles.media.ratio`), how many slides a breakpoint fits
 * (`styles.carousel.per_view`), and where its navigation sits and how big it is
 * (`styles.carousel.nav_position`, `styles.carousel.nav_size`).
 */
class SlidesData extends PropsData
{
    /**
     * @param  SlideItemData[]  $items
     * @param  CtaData[]  $ctas
     */
    public function __construct(
        #[Nullable, Max(255)]
        public ?string $pre = null,
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
