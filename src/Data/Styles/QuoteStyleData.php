<?php

namespace OiLab\OiLaravelPublish\Data\Styles;

use OiLab\OiLaravelPublish\Enums\TextScale;
use Spatie\LaravelData\Data;

/**
 * Presentation of a pulled quote, which varies by typographic scale only.
 */
class QuoteStyleData extends Data
{
    public function __construct(
        public TextScale $size = TextScale::Lg,
    ) {}
}
