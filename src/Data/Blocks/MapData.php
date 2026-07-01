<?php

namespace OiLab\OiLaravelPublish\Data\Blocks;

use OiLab\OiLaravelPublish\Data\PropsData;
use Spatie\LaravelData\Attributes\Validation\Between;
use Spatie\LaravelData\Attributes\Validation\Max;
use Spatie\LaravelData\Attributes\Validation\Nullable;

/**
 * Props for a "map" block.
 */
class MapData extends PropsData
{
    public function __construct(
        #[Between(-90, 90)]
        public float $latitude = 0.0,
        #[Between(-180, 180)]
        public float $longitude = 0.0,
        #[Between(0, 22)]
        public int $zoom = 12,
        #[Nullable, Max(255)]
        public ?string $marker_label = null,
        #[Nullable, Max(255)]
        public ?string $provider = null,
    ) {}
}
