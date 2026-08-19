<?php

namespace OiLab\OiLaravelPublish\Data\Items;

use Spatie\LaravelData\Attributes\Validation\Between;
use Spatie\LaravelData\Attributes\Validation\Max;
use Spatie\LaravelData\Attributes\Validation\Nullable;
use Spatie\LaravelData\Data;

/**
 * A single pin on a "map" block.
 *
 * `title` and `text` were `label` and `description`. `latitude` and `longitude`
 * come after the shared vocabulary: they are the marker's own domain, not an
 * exception to the order.
 *
 * `icon` names a **lucide** icon, in kebab-case, resolved by `DynamicIcon` —
 * the docblock used to promise a file under `public/images/markers/`, which the
 * code has never read.
 */
class MapMarkerData extends Data
{
    public function __construct(
        #[Nullable, Max(255)]
        public ?string $title = null,
        #[Nullable]
        public ?string $text = null,
        #[Nullable, Max(255)]
        public ?string $icon = null,
        #[Between(-90, 90)]
        public float $latitude = 0.0,
        #[Between(-180, 180)]
        public float $longitude = 0.0,
    ) {}
}
