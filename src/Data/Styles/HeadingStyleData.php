<?php

namespace OiLab\OiLaravelPublish\Data\Styles;

use OiLab\OiLaravelPublish\Enums\HeadingTag;
use OiLab\OiLaravelPublish\Enums\HorizontalAlign;
use Spatie\LaravelData\Data;

/**
 * Presentation of a heading: the tag it takes in the document outline, and how
 * it is aligned. The visual scale is a concern of the host's CSS, not of the
 * tag — an `h3` may well render larger than an `h2`.
 */
class HeadingStyleData extends Data
{
    public function __construct(
        public HeadingTag $tag = HeadingTag::H2,
        public HorizontalAlign $align = HorizontalAlign::Left,
    ) {}
}
