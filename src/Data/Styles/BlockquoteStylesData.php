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
        public BlockStyleData $block = new BlockStyleData,
        public QuoteStyleData $quote = new QuoteStyleData,
        public CtasStyleData $ctas = new CtasStyleData,
    ) {}
}
