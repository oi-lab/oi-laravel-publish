<?php

namespace OiLab\OiLaravelPublish\Data\Styles;

use OiLab\OiLaravelPublish\Enums\BlockAlignItems;
use OiLab\OiLaravelPublish\Enums\BlockHeight;
use OiLab\OiLaravelPublish\Enums\BlockJustify;
use OiLab\OiLaravelPublish\Enums\BlockTheme;
use Spatie\LaravelData\Data;

/**
 * Presentation of the block as a whole: how tall it is, how its content sits
 * inside it, and which colour scheme it renders under.
 */
class BlockStyleData extends Data
{
    public function __construct(
        public BlockHeight $height = BlockHeight::Inherit,
        public BlockAlignItems $items = BlockAlignItems::Start,
        public BlockJustify $justify = BlockJustify::Start,
        public BlockTheme $theme = BlockTheme::Light,
    ) {}
}
