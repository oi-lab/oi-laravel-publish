<?php

namespace OiLab\OiLaravelPublish\Data\Blocks;

use OiLab\OiLaravelPublish\Data\PropsData;
use OiLab\OiLaravelPublish\Data\Styles\BreadcrumbStylesData;

/**
 * Props for a "breadcrumb" block. Navigation carries no calls to action, and
 * authors nothing at all: the trail is derived from the page tree, so the only
 * thing left to decide is how the strip is presented.
 *
 * It used to declare an `items` collection, which no screen ever edited and no
 * component ever read — the trail has always come from
 * `PublishPageBreadcrumbService`. {@see BreadcrumbItemData} stays: it is the
 * shape of one derived crumb, and what the browser's `Crumb` type is generated
 * from.
 */
class BreadcrumbData extends PropsData
{
    public function __construct(
        public BreadcrumbStylesData $styles = new BreadcrumbStylesData,
    ) {}
}
