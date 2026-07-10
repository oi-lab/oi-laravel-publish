<?php

namespace OiLab\OiLaravelPublish\Data\Blocks;

use OiLab\OiLaravelPublish\Data\CtaData;
use Spatie\LaravelData\Attributes\Validation\Max;
use Spatie\LaravelData\Attributes\Validation\Nullable;
use Spatie\LaravelData\Data;

/**
 * Caption/CTA metadata for a single slide. The slide image itself comes from the
 * block's `slides` attachment collection, matched by position.
 *
 * A slide has one call to action and no slot to place it in, so `cta` is a
 * single {@see CtaData} whose `position` stays null.
 */
class SlideItemData extends Data
{
    public function __construct(
        #[Nullable, Max(255)]
        public ?string $title = null,
        #[Nullable]
        public ?string $caption = null,
        public ?CtaData $cta = null,
    ) {}
}
