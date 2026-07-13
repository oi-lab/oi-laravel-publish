<?php

namespace OiLab\OiLaravelPublish\Data\Blocks;

use Spatie\LaravelData\Attributes\Validation\Max;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Data;

/**
 * A single question/answer pair inside a "faqs" block.
 *
 * The answer is markdown, rendered with the configured renderer.
 */
class FaqItemData extends Data
{
    public function __construct(
        #[Required, Max(255)]
        public string $question,
        #[Required]
        public string $answer,
    ) {}
}
