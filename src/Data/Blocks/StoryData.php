<?php

namespace OiLab\OiLaravelPublish\Data\Blocks;

use OiLab\OiLaravelPublish\Data\CtaData;
use OiLab\OiLaravelPublish\Data\Items\StoryItemData;
use OiLab\OiLaravelPublish\Data\PropsData;
use OiLab\OiLaravelPublish\Data\Styles\StoryStylesData;
use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Attributes\Validation\Max;
use Spatie\LaravelData\Attributes\Validation\Nullable;

/**
 * Props for a "story" block: a connected sequence of steps laid out along a
 * central rail.
 *
 * The title and lead come from the block's `name` and `excerpt` columns, and
 * the conclusion from its `description`. How many steps sit side by side and
 * the ratio their images fall back to are styles, not content — see
 * `styles.list.columns` and `styles.media.ratio`.
 */
class StoryData extends PropsData
{
    /**
     * @param  StoryItemData[]  $items
     * @param  CtaData[]  $ctas
     */
    public function __construct(
        #[Nullable, Max(255)]
        public ?string $pre = null,
        #[DataCollectionOf(StoryItemData::class)]
        public array $items = [],
        #[DataCollectionOf(CtaData::class)]
        public array $ctas = [],
        public StoryStylesData $styles = new StoryStylesData,
    ) {}
}
