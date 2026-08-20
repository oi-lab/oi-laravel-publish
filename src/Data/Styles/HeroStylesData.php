<?php

namespace OiLab\OiLaravelPublish\Data\Styles;

use Spatie\LaravelData\Data;

/**
 * Presentation of a "hero" block.
 *
 * Slots are composed, never inherited: oi-laravel-ts reads only the constructor
 * of the class it reflects, so an inherited property would vanish from the
 * generated interface without warning.
 */
class HeroStylesData extends Data
{
    public function __construct(
        public ?BlockStyleData $block = null,
        public ?PreStyleData $pre = null,
        public ?HeadingStyleData $title = null,
        public ?TextStyleData $excerpt = null,
        public ?TextStyleData $body = null,
        public ?MediaStyleData $media = null,
        public ?CtasStyleData $ctas = null,
    ) {}
}
