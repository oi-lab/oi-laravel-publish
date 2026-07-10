<?php

namespace OiLab\OiLaravelPublish\Data\Blocks;

use Spatie\LaravelData\Attributes\Validation\Between;
use Spatie\LaravelData\Attributes\Validation\Max;
use Spatie\LaravelData\Attributes\Validation\Nullable;
use Spatie\LaravelData\Data;

/**
 * A single pin on a "map" block.
 *
 * `icon` names a file under `public/images/markers/`; it is a filename, not a
 * path, so the host stays free to serve the directory from wherever it likes.
 */
class MapMarkerData extends Data
{
    public function __construct(
        #[Between(-90, 90)]
        public float $latitude = 0.0,
        #[Between(-180, 180)]
        public float $longitude = 0.0,
        #[Nullable, Max(255)]
        public ?string $label = null,
        #[Nullable]
        public ?string $description = null,
        #[Nullable, Max(255)]
        public ?string $icon = null,
    ) {}
}
