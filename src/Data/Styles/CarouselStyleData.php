<?php

namespace OiLab\OiLaravelPublish\Data\Styles;

use Spatie\LaravelData\Data;

/**
 * Presentation of a carousel: how many slides are visible at each breakpoint.
 * The host maps that count onto its own `basis-*` classes.
 */
class CarouselStyleData extends Data
{
    public function __construct(
        public BreakpointsData $per_view = new BreakpointsData,
    ) {}
}
