<?php

namespace OiLab\OiLaravelPublish\Data\Blocks;

use OiLab\OiLaravelPublish\Data\PropsData;
use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Attributes\Validation\Max;
use Spatie\LaravelData\Attributes\Validation\Required;

/**
 * Props for a "warranty" block: an introduction plus a list of warranty items.
 * The illustration is supplied through the block's `cover` attachment
 * collection, not these props.
 *
 * @property array<int, WarrantyItemData> $items
 */
class WarrantyData extends PropsData
{
    /**
     * @param  array<int, WarrantyItemData>  $items
     */
    public function __construct(
        #[Required, Max(255)]
        public string $pre,
        #[Required, Max(255)]
        public string $title,
        #[Required]
        public string $overview,
        #[DataCollectionOf(WarrantyItemData::class)]
        public array $items = [],
    ) {}
}
