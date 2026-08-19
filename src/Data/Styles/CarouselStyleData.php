<?php

namespace OiLab\OiLaravelPublish\Data\Styles;

use OiLab\OiLaravelPublish\Enums\SlideNavPosition;
use OiLab\OiLaravelPublish\Enums\SlideNavSize;
use Spatie\LaravelData\Data;

/**
 * Presentation of a carousel: how many slides are visible at each breakpoint,
 * and where its navigation sits and how big it is.
 *
 * The two navigation properties used to hang flat off `SlidesStylesData`, beside
 * the slots rather than inside one, and no component read them. They belong to
 * the carousel, which is the only thing that has a navigation.
 */
class CarouselStyleData extends Data
{
    public function __construct(
        public BreakpointsData $per_view = new BreakpointsData,
        public SlideNavPosition $nav_position = SlideNavPosition::Bottom,
        public SlideNavSize $nav_size = SlideNavSize::Medium,
    ) {}
}
