<?php

namespace OiLab\OiLaravelPublish\Data\Styles;

use OiLab\OiLaravelPublish\Enums\HorizontalAlign;
use OiLab\OiLaravelPublish\Enums\TextScale;
use Spatie\LaravelData\Data;

/**
 * Presentation of a block's group of calls to action.
 *
 * `size` is the group's typographic scale; each `CtaData` still carries its own
 * button `size`, so a primary and a secondary action can differ within a group.
 */
class CtasStyleData extends Data
{
    public function __construct(
        public TextScale $size = TextScale::Base,
        public HorizontalAlign $align = HorizontalAlign::Left,
    ) {}
}
