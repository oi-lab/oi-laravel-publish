<?php

namespace OiLab\OiLaravelPublish\Data\Blocks;

use OiLab\OiLaravelPublish\Data\PropsData;
use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Attributes\Validation\Max;
use Spatie\LaravelData\Attributes\Validation\Min;
use Spatie\LaravelData\Attributes\Validation\Nullable;

/**
 * Props for a "features" block: an introduction plus a grid of feature items.
 */
class FeaturesData extends PropsData
{
    /**
     * @param  array<int, FeatureItemData>  $items
     */
    public function __construct(
        #[Nullable, Max(255)]
        public ?string $heading = null,
        #[Nullable]
        public ?string $intro = null,
        #[Min(1), Max(6)]
        public int $columns = 3,
        #[DataCollectionOf(FeatureItemData::class)]
        public array $items = [],
    ) {}
}
