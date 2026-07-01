<?php

namespace OiLab\OiLaravelPublish\Data\Blocks;

use Spatie\LaravelData\Attributes\Validation\Max;
use Spatie\LaravelData\Attributes\Validation\Nullable;
use Spatie\LaravelData\Data;

/**
 * Caption/CTA metadata for a single slide. The slide image itself comes from the
 * block's `slides` attachment collection, matched by position.
 */
class SlideItemData extends Data
{
    public function __construct(
        #[Nullable, Max(255)]
        public ?string $title = null,
        #[Nullable]
        public ?string $caption = null,
        #[Nullable, Max(255)]
        public ?string $cta_label = null,
        #[Nullable, Max(2048)]
        public ?string $cta_url = null,
    ) {}
}
