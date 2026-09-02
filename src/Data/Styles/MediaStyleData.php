<?php

namespace OiLab\OiLaravelPublish\Data\Styles;

use OiLab\OiLaravelPublish\Enums\BlockMarginX;
use OiLab\OiLaravelPublish\Enums\BlockMarginY;
use OiLab\OiLaravelPublish\Enums\BlockWidth;
use OiLab\OiLaravelPublish\Enums\CoverLayout;
use OiLab\OiLaravelPublish\Enums\MediaRatio;
use Spatie\LaravelData\Data;

/**
 * Presentation of the media a block carries — its cover image, or the video
 * that takes the same slot: where it sits against the content it illustrates,
 * the shape it is shown at, and the column it is drawn in.
 *
 * `layout` is nullable on purpose. A block whose media has no place to choose —
 * a carousel, or a pool of item images — declares the slot for its ratio alone,
 * and the console then offers only that. `null` reads as «this block arranges
 * its media itself».
 *
 * `width`, `margin_x` and `margin_y` are the same three an area carries (see
 * {@see BlockAreaStyleData}), and for the same reason: the media slot is a
 * column across the section like the header and the body are, and it used to be
 * the only one an author could not narrow, place or space. A video pasted into
 * a block spanned the whole section while the text beside it was capped at
 * `md`. The defaults are what the figure was before the choice existed — full
 * width, no margin of its own — so no existing block moves.
 */
class MediaStyleData extends Data
{
    public function __construct(
        public ?CoverLayout $layout = CoverLayout::Right,
        public MediaRatio $ratio = MediaRatio::Inherit,
        public BlockWidth $width = BlockWidth::Full,
        /** Only bites once `width` caps the figure: a full-width one has no slack to sit in. */
        public BlockMarginX $margin_x = BlockMarginX::Auto,
        public BlockMarginY $margin_y = BlockMarginY::None,
    ) {}
}
