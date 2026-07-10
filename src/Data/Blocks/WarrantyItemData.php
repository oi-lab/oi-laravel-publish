<?php

namespace OiLab\OiLaravelPublish\Data\Blocks;

use Spatie\LaravelData\Attributes\Validation\Max;
use Spatie\LaravelData\Attributes\Validation\Nullable;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Data;

/**
 * A single item inside a "warranty" block.
 */
class WarrantyItemData extends Data
{
    public function __construct(
        #[Required, Max(255)]
        public string $title,
        #[Nullable]
        public ?string $text = null,
        #[Nullable, Max(255)]
        public ?string $icon = null,
    ) {}
}
