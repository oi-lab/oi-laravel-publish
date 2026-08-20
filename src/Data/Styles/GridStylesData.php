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
        public ?BlockSectionStyleData $block = null,
        public ?BlockAreaStyleData $header_area = null,
        public ?PreStyleData $pre = null,
        public ?HeadingStyleData $title = null,
        public ?TextStyleData $excerpt = null,
        public ?BlockAreaStyleData $body_area = null,
        public ?MediaStyleData $media = null,
        public ?ListStyleData $list = null,
        public ?CtasStyleData $ctas = null,
        public ?BlockAreaStyleData $footer_area = null,
    ) {}
}
