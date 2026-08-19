<?php

namespace OiLab\OiLaravelPublish\Data\Styles;

use Spatie\LaravelData\Data;

/**
 * Presentation of a "content" block.
 *
 * `body` was called `description` — the name of the column it styles rather
 * than of the role it plays. It was the last slot named after a column.
 *
 * Slots are composed, never inherited: oi-laravel-ts reads only the constructor
 * of the class it reflects, so an inherited property would vanish from the
 * generated interface without warning.
 */
class ContentStylesData extends Data
{
    public function __construct(
        public BlockStyleData $block = new BlockStyleData,
        public PreStyleData $pre = new PreStyleData,
        public HeadingStyleData $title = new HeadingStyleData,
        public TextStyleData $excerpt = new TextStyleData,
        public TextStyleData $body = new TextStyleData,
        public MediaStyleData $media = new MediaStyleData,
        public CtasStyleData $ctas = new CtasStyleData,
    ) {}
}
