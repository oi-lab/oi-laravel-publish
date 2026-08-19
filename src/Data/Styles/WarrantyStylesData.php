<?php

namespace OiLab\OiLaravelPublish\Data\Styles;

use OiLab\OiLaravelPublish\Enums\CoverLayout;
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
        public BlockStyleData $block = new BlockStyleData,
        public PreStyleData $pre = new PreStyleData,
        public HeadingStyleData $title = new HeadingStyleData,
        public TextStyleData $excerpt = new TextStyleData,
        public MediaStyleData $media = new MediaStyleData(layout: CoverLayout::Before),
        public ListStyleData $list = new ListStyleData,
        public CtasStyleData $ctas = new CtasStyleData,
    ) {}
}
