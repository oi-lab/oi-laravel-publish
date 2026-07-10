<?php

namespace OiLab\OiLaravelPublish\Data\Blocks;

use OiLab\OiLaravelPublish\Data\CtaData;
use OiLab\OiLaravelPublish\Data\PropsData;
use OiLab\OiLaravelPublish\Data\Styles\WarrantyStylesData;
use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Attributes\Validation\Max;
use Spatie\LaravelData\Attributes\Validation\Nullable;

/**
 * Props for a "warranty" block: a list of warranty items.
 *
 * The title and lead come from the block's `name` and `excerpt` columns. The
 * illustration is supplied through the block's `cover` attachment collection.
 */
class WarrantyData extends PropsData
{
    /**
     * @param  WarrantyItemData[]  $items
     * @param  CtaData[]  $ctas
     */
    public function __construct(
        #[Nullable, Max(255)]
        public ?string $pre = null,
        #[DataCollectionOf(WarrantyItemData::class)]
        public array $items = [],
        #[DataCollectionOf(CtaData::class)]
        public array $ctas = [],
        public WarrantyStylesData $styles = new WarrantyStylesData,
    ) {}
}
