<?php

namespace OiLab\OiLaravelPublish\Data\Styles;

use OiLab\OiLaravelPublish\Enums\SlideNavPosition;
use OiLab\OiLaravelPublish\Enums\SlideNavSize;
use Spatie\LaravelData\Data;

/**
 * Presentation of a "slides" block and of its carousel.
 *
 * Slots are composed, never inherited: oi-laravel-ts reads only the constructor
 * of the class it reflects, so an inherited property would vanish from the
 * generated interface without warning.
 */
class SlidesStylesData extends Data
{
    public function __construct(
        public BlockStyleData $block = new BlockStyleData,
        public HeadingStyleData $title = new HeadingStyleData,
        public CtasStyleData $ctas = new CtasStyleData,
        public SlideNavPosition $nav_position = SlideNavPosition::Bottom,
        public SlideNavSize $nav_size = SlideNavSize::Medium,
        public CarouselStyleData $carousel = new CarouselStyleData,
    ) {}
}
