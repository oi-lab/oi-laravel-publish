<?php

namespace OiLab\OiLaravelPublish\Data\Blocks;

use Spatie\LaravelData\Attributes\Validation\Max;
use Spatie\LaravelData\Attributes\Validation\Nullable;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Data;

/**
 * A single crumb in a "breadcrumb" block.
 */
class BreadcrumbItemData extends Data
{
    public function __construct(
        #[Required, Max(255)]
        public string $label,
        #[Nullable, Max(2048)]
        public ?string $url = null,
    ) {}
}
