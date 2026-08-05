<?php

namespace OiLab\OiLaravelPublish\Data\Styles;

use OiLab\OiLaravelPublish\Enums\HorizontalAlign;
use OiLab\OiLaravelPublish\Enums\TextScale;
use Spatie\LaravelData\Data;

/**
 * Presentation of a preformatted block: how it is aligned and what scale of text it uses.
 */
class PreStyleData extends Data
{
    public function __construct(
        public TextScale $size = TextScale::Inherit,
        public HorizontalAlign $align = HorizontalAlign::Left,
    ) {}
}
