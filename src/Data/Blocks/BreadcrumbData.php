<?php

namespace OiLab\OiLaravelPublish\Data\Blocks;

use OiLab\OiLaravelPublish\Data\PropsData;
use OiLab\OiLaravelPublish\Data\Styles\BreadcrumbStylesData;
use Spatie\LaravelData\Attributes\DataCollectionOf;

/**
 * Props for a "breadcrumb" block. Navigation carries no calls to action.
 */
class BreadcrumbData extends PropsData
{
    /**
     * @param  BreadcrumbItemData[]  $items
     */
    public function __construct(
        #[DataCollectionOf(BreadcrumbItemData::class)]
        public array $items = [],
        public BreadcrumbStylesData $styles = new BreadcrumbStylesData,
    ) {}
}
