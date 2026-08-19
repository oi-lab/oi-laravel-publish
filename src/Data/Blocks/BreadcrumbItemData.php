<?php

namespace OiLab\OiLaravelPublish\Data\Blocks;

use Spatie\LaravelData\Attributes\Validation\Max;
use Spatie\LaravelData\Attributes\Validation\Nullable;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Data;

/**
 * A single crumb of the trail a "breadcrumb" block renders.
 *
 * It is not a prop: the trail is derived from the page tree by the host, sent
 * beside the block, and never authored. The class stays because it is the shape
 * the host builds and the one the browser's `Crumb` type is generated from. The
 * last crumb carries no url — it is the page the reader stands on.
 */
class BreadcrumbItemData extends Data
{
    public function __construct(
        #[Required, Max(255)]
        public string $label,
        #[Nullable, Max(2048)]
        public ?string $url = null,
    ) {}
}
