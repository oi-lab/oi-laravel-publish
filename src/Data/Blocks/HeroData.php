<?php

namespace OiLab\OiLaravelPublish\Data\Blocks;

use OiLab\OiLaravelPublish\Data\PropsData;
use Spatie\LaravelData\Attributes\Validation\In;
use Spatie\LaravelData\Attributes\Validation\Max;
use Spatie\LaravelData\Attributes\Validation\Nullable;

/**
 * Props for a "hero" block. The background image is supplied through the block's
 * `cover` attachment collection, not these props.
 */
class HeroData extends PropsData
{
    public function __construct(
        #[Nullable, Max(255)]
        public ?string $heading = null,
        #[Nullable, Max(255)]
        public ?string $subheading = null,
        #[Nullable]
        public ?string $body = null,
        #[Nullable, Max(255)]
        public ?string $cta_label = null,
        #[Nullable, Max(2048)]
        public ?string $cta_url = null,
        #[Nullable, In(['left', 'center', 'right'])]
        public ?string $alignment = 'left',
    ) {}
}
