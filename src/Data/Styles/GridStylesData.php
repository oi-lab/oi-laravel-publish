<?php

namespace OiLab\OiLaravelPublish\Data\Styles;

use Spatie\LaravelData\Data;

/**
 * Presentation of a "grid" block and of its item grid.
 *
 * Its `media` slot carries no layout: the block's images are the `gallery` pool
 * its items draw from, and each item places its own.
 *
 * Slots are composed, never inherited: oi-laravel-ts reads only the constructor
 * of the class it reflects, so an inherited property would vanish from the
 * generated interface without warning.
 */
class GridStylesData extends Data
{
    public function __construct(
        public BlockStyleData $block = new BlockStyleData,
        public PreStyleData $pre = new PreStyleData,
        public HeadingStyleData $title = new HeadingStyleData,
        public TextStyleData $excerpt = new TextStyleData,
        public MediaStyleData $media = new MediaStyleData(layout: null),
        public ListStyleData $list = new ListStyleData,
        public CtasStyleData $ctas = new CtasStyleData,
    ) {}
}
