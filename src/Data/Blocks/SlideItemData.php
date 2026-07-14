<?php

namespace OiLab\OiLaravelPublish\Data\Blocks;

use OiLab\OiLaravelPublish\Data\CtaData;
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
        public ?CtaData $cta = null,
    ) {}
}
