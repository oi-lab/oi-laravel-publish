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
        public ?BlockSectionStyleData $block = null,
        public ?BlockAreaStyleData $header_area = null,
        public ?PreStyleData $pre = null,
        public ?HeadingStyleData $title = null,
        public ?TextStyleData $excerpt = null,
        public ?BlockAreaStyleData $body_area = null,
        public ?TextStyleData $body = null,
        public ?MediaStyleData $media = null,
        public ?ListStyleData $list = null,
        public ?CtasStyleData $ctas = null,
        public ?BlockAreaStyleData $footer_area = null,
    ) {}
}
