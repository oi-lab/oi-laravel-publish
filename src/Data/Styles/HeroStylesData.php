<?php

namespace OiLab\OiLaravelPublish\Data\Styles;

use Spatie\LaravelData\Data;

/**
 * Presentation of a "hero" block.
 *
 * It carries the three structural areas every other template carries — its
 * `block` slot is the {@see BlockSectionStyleData} of a split block, and width,
 * margin_x, padding_y, items, justify and an area's own space_y live on the
 * areas. It was the last template still holding all of them on one
 * {@see BlockStyleData}, which is why its overline, its title and its actions
 * could never be placed apart from one another.
 *
 * What stays its own is the **typography**, not the structure: a hero's
 * overline, title, lead and body are drawn by the `Hero*` components of the host
 * (an `h1` by default where a block's title follows its tag), inside the areas
 * below. Splitting the areas does not touch a published hero's look.
 *
 * Slots are composed, never inherited: oi-laravel-ts reads only the constructor
 * of the class it reflects, so an inherited property would vanish from the
 * generated interface without warning.
 */
class HeroStylesData extends Data
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
        public ?CtasStyleData $ctas = null,
        public ?BlockAreaStyleData $footer_area = null,
    ) {}
}
