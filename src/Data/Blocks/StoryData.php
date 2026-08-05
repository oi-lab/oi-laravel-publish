<?php

namespace OiLab\OiLaravelPublish\Data\Blocks;

use OiLab\OiLaravelPublish\Data\CtaData;
use OiLab\OiLaravelPublish\Data\PropsData;
use OiLab\OiLaravelPublish\Data\Styles\FeaturesStylesData;
use OiLab\OiLaravelPublish\Enums\CoverLayout;
use OiLab\OiLaravelPublish\Enums\MediaRatio;
use Spatie\LaravelData\Attributes\DataCollectionOf;

/**
 * Props for a "features" block: a grid of feature items.
 *
 * The title and lead come from the block's `name` and `excerpt` columns. The
 * column count is a style, not content — see `styles.list.columns`. An optional
 * `cover` attachment is arranged with `cover_layout`.
 */
class StoryData extends PropsData
{
    /**
     * @param  StoryItemData[]  $items
     * @param  CtaData[]  $ctas
     */
    public function __construct(
        #[DataCollectionOf(StoryItemData::class)]
        public array $items = [],
        public CoverLayout $cover_layout = CoverLayout::Right,
        public MediaRatio $cover_ratio = MediaRatio::Inherit,
        #[DataCollectionOf(CtaData::class)]
        public array $ctas = [],
        public FeaturesStylesData $styles = new FeaturesStylesData,
    ) {}
}
