<?php

namespace OiLab\OiLaravelPublish\Data\Blocks;

use OiLab\OiLaravelPublish\Data\CtaData;
use OiLab\OiLaravelPublish\Enums\ItemLayout;
use OiLab\OiLaravelPublish\Enums\MediaRatio;
use Spatie\LaravelData\Attributes\Validation\Max;
use Spatie\LaravelData\Attributes\Validation\Nullable;
use Spatie\LaravelData\Data;

/**
 * Caption/CTA/media metadata for a single slide.
 *
 * A slide is potentially text (`title`, `caption`), potentially a link (a single
 * {@see CtaData}, whose `position` stays null — a slide has no slot to place it
 * in), and potentially one attachment. `attachment_uuid` links the slide to one
 * entry of the block's `slides` attachment collection by that attachment's
 * stable `uuid`; it is null for a text-only slide. The host resolves the image
 * by uuid (not by position) and enforces that the uuid belongs to the block.
 * `item_layout` arranges that image relative to the caption, `max_width`
 * optionally constrains the slide's width, and `cover_ratio` is the slide's
 * aspect ratio; `MediaRatio::Inherit` defers to the block's `media_ratio`.
 */
class SlideItemData extends Data
{
    public function __construct(
        #[Nullable, Max(255)]
        public ?string $title = null,
        #[Nullable]
        public ?string $caption = null,
        #[Nullable, Max(36)]
        public ?string $attachment_uuid = null,
        public ItemLayout $item_layout = ItemLayout::Left,
        #[Nullable, Max(32)]
        public ?string $max_width = null,
        public MediaRatio $cover_ratio = MediaRatio::Inherit,
        public ?CtaData $cta = null,
    ) {}
}
