<?php

namespace OiLab\OiLaravelPublish\Data\Styles;

use Spatie\LaravelData\Data;

/**
 * Presentation of a "warranty" block and of its item list.
 *
 * Slots are composed, never inherited: oi-laravel-ts reads only the constructor
 * of the class it reflects, so an inherited property would vanish from the
 * generated interface without warning.
 */
class WarrantyStylesData extends Data
{
    public function __construct(
        public BlockStyleData $block = new BlockStyleData,
        public HeadingStyleData $title = new HeadingStyleData,
        public TextStyleData $excerpt = new TextStyleData,
        public CtasStyleData $ctas = new CtasStyleData,
        public ListStyleData $list = new ListStyleData,
    ) {}
}
