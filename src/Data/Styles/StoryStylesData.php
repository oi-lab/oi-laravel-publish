<?php

namespace OiLab\OiLaravelPublish\Data\Styles;

use Spatie\LaravelData\Data;

/**
 * Presentation of a "story" block and of its item rail.
 *
 * Its `media` slot carries no layout, for the same reason a `grid` one does
 * not: the block's images are the `gallery` pool its steps draw from.
 *
 * `list.columns` is declared but not read: the rail is a fixed three-column
 * grid (rule S4). The slot stays so the class keeps the shape of its siblings;
 * the console stops offering the count for this block.
 *
 * Slots are composed, never inherited: oi-laravel-ts reads only the constructor
 * of the class it reflects, so an inherited property would vanish from the
 * generated interface without warning.
 */
class StoryStylesData extends Data
{
    public function __construct(
        public BlockStyleData $block = new BlockStyleData,
        public PreStyleData $pre = new PreStyleData,
        public HeadingStyleData $title = new HeadingStyleData,
        public TextStyleData $excerpt = new TextStyleData,
        public TextStyleData $body = new TextStyleData,
        public MediaStyleData $media = new MediaStyleData(layout: null),
        public ListStyleData $list = new ListStyleData,
        public CtasStyleData $ctas = new CtasStyleData,
    ) {}
}
