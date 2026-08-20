<?php

namespace OiLab\OiLaravelPublish\Data\Styles;

use OiLab\OiLaravelPublish\Enums\BlockHeight;
use OiLab\OiLaravelPublish\Enums\BlockMarginY;
use OiLab\OiLaravelPublish\Enums\BlockSpaceX;
use OiLab\OiLaravelPublish\Enums\BlockSpaceY;
use OiLab\OiLaravelPublish\Enums\BlockTheme;
use Spatie\LaravelData\Data;

/**
 * Presentation of a block's own section, once its header, its body and its
 * footer are independently styled by {@see BlockAreaStyleData}: how tall the
 * section runs, the rhythm that separates it from its neighbours on the page,
 * the gap between its three areas, the gutter between its content and its
 * media, and which colour scheme it renders under.
 *
 * `space_y` here is not {@see BlockAreaStyleData::$space_y}: an area's own
 * spaces its area's *own* children — a header's overline and title — while
 * this one spaces the header, the body and the footer apart from one another.
 * The two used to be the same value, back when a block was one flex column
 * rather than three.
 *
 * The block templates that carry three areas ({@see BlockquoteStylesData} and
 * its siblings) compose this instead of the full {@see BlockStyleData}: width,
 * margin_x, padding_y, an area's own space_y, items and justify moved to the
 * areas, where a header, a body and a footer can finally differ. `hero`,
 * `breadcrumb` and `reassurance` are not among them — they keep a single
 * {@see BlockStyleData} slot, unsplit, so they keep every field on it.
 */
class BlockSectionStyleData extends Data
{
    public function __construct(
        public BlockHeight $height = BlockHeight::Inherit,
        public BlockMarginY $margin_y = BlockMarginY::Medium,
        public BlockSpaceX $space_x = BlockSpaceX::Medium,
        public BlockSpaceY $space_y = BlockSpaceY::Medium,
        public BlockTheme $theme = BlockTheme::Light,
    ) {}
}
