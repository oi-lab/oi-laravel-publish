<?php

namespace OiLab\OiLaravelPublish\Data\Styles;

use Spatie\LaravelData\Data;

/**
 * Presentation of a "blockquote" block.
 *
 * Slots are composed, never inherited: oi-laravel-ts reads only the constructor
 * of the class it reflects, so an inherited property would vanish from the
 * generated interface without warning.
 */
class BlockquoteStylesData extends Data
{
    public function __construct(
        public ?BlockSectionStyleData $block = null,
        public ?BlockAreaStyleData $header_area = null,
        public ?BlockAreaStyleData $body_area = null,
        public ?QuoteStyleData $quote = null,
        public ?CtasStyleData $ctas = null,
        public ?BlockAreaStyleData $footer_area = null,
    ) {}
}
