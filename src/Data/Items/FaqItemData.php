<?php

namespace OiLab\OiLaravelPublish\Data\Items;

use Spatie\LaravelData\Attributes\Validation\Max;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Data;

/**
 * A single question/answer pair inside a "faqs" block.
 *
 * `title` is the question, `text` the answer — the same two words every other
 * element uses. They were `question` and `answer`, which said nothing a shared
 * editor or a shared markdown renderer could act on.
 *
 * `text` is the one body of the catalogue that is **required**: a question
 * without an answer is not a FAQ.
 */
class FaqItemData extends Data
{
    public function __construct(
        #[Required, Max(255)]
        public string $title,
        #[Required]
        public string $text,
    ) {}
}
