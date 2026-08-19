<?php

namespace OiLab\OiLaravelPublish\Data\Styles;

use Spatie\LaravelData\Data;

/**
 * Presentation of a "slides" block and of its carousel.
 *
 * `nav_position` and `nav_size` used to sit flat beside the slots; they belong
 * to the `carousel` slot, which is what has a navigation. The `media` slot
 * carries no layout — a carousel places its own images.
 *
 * Slots are composed, never inherited: oi-laravel-ts reads only the constructor
 * of the class it reflects, so an inherited property would vanish from the
 * generated interface without warning.
 */
class SlidesStylesData extends Data
{
    public function __construct(
        public BlockStyleData $block = new BlockStyleData,
        public PreStyleData $pre = new PreStyleData,
        public HeadingStyleData $title = new HeadingStyleData,
        public TextStyleData $excerpt = new TextStyleData,
        public MediaStyleData $media = new MediaStyleData(layout: null),
        public CarouselStyleData $carousel = new CarouselStyleData,
        public CtasStyleData $ctas = new CtasStyleData,
    ) {}
}
