<?php

namespace OiLab\OiLaravelPublish\Data\Styles;

use OiLab\OiLaravelPublish\Enums\ListMarker;
use OiLab\OiLaravelPublish\Enums\ListType;
use Spatie\LaravelData\Attributes\Validation\Max;
use Spatie\LaravelData\Attributes\Validation\Nullable;
use Spatie\LaravelData\Data;

/**
 * Presentation of a block that repeats items — features, warranty points.
 *
 * `marker_icon` is only read when `marker` is `svg`; it names a file under
 * `public/images/markers/`.
 */
class ListStyleData extends Data
{
    public function __construct(
        public BreakpointsData $columns = new BreakpointsData,
        public ListType $type = ListType::Unordered,
        public ListMarker $marker = ListMarker::Disc,
        #[Nullable, Max(255)]
        public ?string $marker_icon = null,
    ) {}
}
