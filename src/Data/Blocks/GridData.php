<?php

namespace OiLab\OiLaravelPublish\Data\Blocks;

use OiLab\OiLaravelPublish\Data\CtaData;
use OiLab\OiLaravelPublish\Data\Items\GridItemData;
use OiLab\OiLaravelPublish\Data\PropsData;
use OiLab\OiLaravelPublish\Data\Styles\GridStylesData;
use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Attributes\Validation\Max;
use Spatie\LaravelData\Attributes\Validation\Nullable;

/**
 * Props for a "grid" block: a grid of items.
 *
 * The title and lead come from the block's `name` and `excerpt` columns. The
 * column count is a style, not content — see `styles.list.columns` — and so is
 * the ratio the item images fall back to, in `styles.media.ratio`.
 */
class GridData extends PropsData
{
    /**
     * @param  GridItemData[]  $items
     * @param  CtaData[]  $ctas
     */
    public function __construct(
        #[Nullable, Max(255)]
        public ?string $pre = null,
        #[DataCollectionOf(GridItemData::class)]
        public array $items = [],
        #[DataCollectionOf(CtaData::class)]
        public array $ctas = [],
        public GridStylesData $styles = new GridStylesData,
    ) {}
}
