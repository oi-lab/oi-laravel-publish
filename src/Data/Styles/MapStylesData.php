<?php

namespace OiLab\OiLaravelPublish\Data\Styles;

use Spatie\LaravelData\Data;

/**
 * Presentation of a "map" block.
 *
 * No layout slot for its markers: a pin is placed by its coordinates, not by a
 * grid.
 *
 * Slots are composed, never inherited: oi-laravel-ts reads only the constructor
 * of the class it reflects, so an inherited property would vanish from the
 * generated interface without warning.
 */
class MapStylesData extends Data
{
    public function __construct(
        public BlockStyleData $block = new BlockStyleData,
        public PreStyleData $pre = new PreStyleData,
        public HeadingStyleData $title = new HeadingStyleData,
        public TextStyleData $excerpt = new TextStyleData,
        public CtasStyleData $ctas = new CtasStyleData,
    ) {}
}
