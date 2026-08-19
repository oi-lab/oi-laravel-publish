<?php

namespace OiLab\OiLaravelPublish\Data\Styles;

use OiLab\OiLaravelPublish\Enums\CoverLayout;
use OiLab\OiLaravelPublish\Enums\MediaRatio;
use Spatie\LaravelData\Data;

/**
 * Presentation of the media a block carries: where it sits against the content
 * it illustrates, and the shape it is shown at.
 *
 * `layout` is nullable on purpose. A block whose media has no place to choose —
 * a carousel, or a pool of item images — declares the slot for its ratio alone,
 * and the console then offers only that. `null` reads as «this block arranges
 * its media itself».
 */
class MediaStyleData extends Data
{
    public function __construct(
        public ?CoverLayout $layout = CoverLayout::Right,
        public MediaRatio $ratio = MediaRatio::Inherit,
    ) {}
}
