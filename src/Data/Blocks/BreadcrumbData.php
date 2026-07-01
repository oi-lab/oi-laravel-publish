<?php

namespace OiLab\OiLaravelPublish\Data\Blocks;

use OiLab\OiLaravelPublish\Data\PropsData;
use Spatie\LaravelData\Attributes\DataCollectionOf;

/**
 * Props for a "breadcrumb" block.
 *
 * @property array<int, BreadcrumbItemData> $items
 */
class BreadcrumbData extends PropsData
{
    /**
     * @param  array<int, BreadcrumbItemData>  $items
     */
    public function __construct(
        #[DataCollectionOf(BreadcrumbItemData::class)]
        public array $items = [],
    ) {}
}
