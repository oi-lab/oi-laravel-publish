<?php

namespace OiLab\OiLaravelPublish\Data\Styles;

use OiLab\OiLaravelPublish\Enums\HorizontalAlign;
use Spatie\LaravelData\Data;

/**
 * Presentation of a plain text run — a lead paragraph, a rich body.
 */
class TextStyleData extends Data
{
    public function __construct(
        public HorizontalAlign $align = HorizontalAlign::Left,
    ) {}
}
