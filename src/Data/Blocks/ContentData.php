<?php

namespace OiLab\OiLaravelPublish\Data\Blocks;

use OiLab\OiLaravelPublish\Data\PropsData;
use Spatie\LaravelData\Attributes\Validation\In;
use Spatie\LaravelData\Attributes\Validation\Nullable;
use Spatie\LaravelData\Attributes\Validation\Required;

/**
 * Props for a free-form "content" block rendered with the configured renderer.
 */
class ContentData extends PropsData
{
    public function __construct(
        #[Required]
        public string $body,
        #[Nullable, In(['markdown', 'html'])]
        public ?string $format = 'markdown',
    ) {}
}
