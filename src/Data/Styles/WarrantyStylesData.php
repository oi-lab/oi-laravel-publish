<?php

namespace OiLab\OiLaravelPublish\Data\Styles;

use Spatie\LaravelData\Data;

/**
 * Presentation of a "warranty" block and of its item list.
 *
 * Its cover stacked above the list before it was a choice, so `media.layout`
 * starts there rather than at the shared `right`.
 *
 * Slots are composed, never inherited: oi-laravel-ts reads only the constructor
 * of the class it reflects, so an inherited property would vanish from the
 * generated interface without warning.
 */
class WarrantyStylesData extends Data
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
