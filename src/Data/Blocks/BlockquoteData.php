<?php

namespace OiLab\OiLaravelPublish\Data\Blocks;

use OiLab\OiLaravelPublish\Data\PropsData;
use Spatie\LaravelData\Attributes\Validation\Max;
use Spatie\LaravelData\Attributes\Validation\Nullable;
use Spatie\LaravelData\Attributes\Validation\Required;

/**
 * Props for a "blockquote" block.
 */
class BlockquoteData extends PropsData
{
    public function __construct(
        #[Required]
        public string $quote,
        #[Nullable, Max(255)]
        public ?string $author = null,
        #[Nullable, Max(255)]
        public ?string $role = null,
        #[Nullable, Max(2048)]
        public ?string $source_url = null,
    ) {}
}
