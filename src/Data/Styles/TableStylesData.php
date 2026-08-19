<?php

namespace OiLab\OiLaravelPublish\Data\Styles;

use Spatie\LaravelData\Data;

/**
 * Presentation of a "table" block.
 *
 * Slots are composed, never inherited: oi-laravel-ts reads only the constructor
 * of the class it reflects, so an inherited property would vanish from the
 * generated interface without warning.
 */
class TableStylesData extends Data
{
    public function __construct(
        public BlockStyleData $block = new BlockStyleData,
        public PreStyleData $pre = new PreStyleData,
        public HeadingStyleData $title = new HeadingStyleData,
        public TextStyleData $excerpt = new TextStyleData,
        public TableStyleData $table = new TableStyleData,
        public CtasStyleData $ctas = new CtasStyleData,
    ) {}
}
