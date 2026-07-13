<?php

namespace OiLab\OiLaravelPublish\Data\Styles;

use Spatie\LaravelData\Data;

/**
 * Presentation of a "faqs" block and of its question list.
 *
 * Mirrors FeaturesStylesData without the `ctas` slot: a FAQ carries no calls to
 * action. Slots are composed, never inherited: oi-laravel-ts reads only the
 * constructor of the class it reflects, so an inherited property would vanish
 * from the generated interface without warning.
 */
class FaqsStylesData extends Data
{
    public function __construct(
        public BlockStyleData $block = new BlockStyleData,
        public HeadingStyleData $title = new HeadingStyleData,
        public TextStyleData $excerpt = new TextStyleData,
        public ListStyleData $list = new ListStyleData,
    ) {}
}
