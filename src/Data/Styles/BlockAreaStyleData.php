<?php

namespace OiLab\OiLaravelPublish\Data\Styles;

use OiLab\OiLaravelPublish\Enums\BlockAlignItems;
use OiLab\OiLaravelPublish\Enums\BlockJustify;
use OiLab\OiLaravelPublish\Enums\BlockMarginX;
use OiLab\OiLaravelPublish\Enums\BlockMarginY;
use OiLab\OiLaravelPublish\Enums\BlockPaddingY;
use OiLab\OiLaravelPublish\Enums\BlockSpaceY;
use OiLab\OiLaravelPublish\Enums\BlockWidth;
use Spatie\LaravelData\Data;

/**
 * Presentation of one structural area of a block — its header, its body, or its
 * footer: how wide its column runs, how it sits against the areas around it, and
 * how its own children are laid out.
 *
 * A block that carries three of these — one header, one body, one footer —
 * composes {@see BlockSectionStyleData} instead of the full
 * {@see BlockStyleData} for its own section: height, theme and the media gutter
 * stay there, described once for the block rather than once per area.
 * `breadcrumb` and `reassurance` keep the single, unsplit
 * {@see BlockStyleData} and never reach for this class. `hero` does reach for
 * it: it carries the three areas, and draws its own typography inside them.
 *
 * `margin_y` here is not {@see BlockStyleData::$margin_y} /
 * {@see BlockSectionStyleData::$margin_y}: the block's own is the page rhythm
 * between it and its neighbours, unchanged by this class. It defaults to
 * `none`, an additive margin an author opts into to open space around one
 * area — between the header and the body, say — rather than a value every
 * area starts from.
 */
class BlockAreaStyleData extends Data
{
    public function __construct(
        public BlockWidth $width = BlockWidth::Medium,
        public BlockMarginX $margin_x = BlockMarginX::Auto,
        public BlockMarginY $margin_y = BlockMarginY::None,
        public BlockPaddingY $padding_y = BlockPaddingY::None,
        public BlockSpaceY $space_y = BlockSpaceY::Medium,
        public BlockAlignItems $items = BlockAlignItems::Start,
        public BlockJustify $justify = BlockJustify::Start,
    ) {}
}
