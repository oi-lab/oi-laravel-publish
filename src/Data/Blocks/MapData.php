<?php

namespace OiLab\OiLaravelPublish\Data\Blocks;

use OiLab\OiLaravelPublish\Data\CtaData;
use OiLab\OiLaravelPublish\Data\Items\MapMarkerData;
use OiLab\OiLaravelPublish\Data\PropsData;
use OiLab\OiLaravelPublish\Data\Styles\MapStylesData;
use OiLab\OiLaravelPublish\Enums\MapProvider;
use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Attributes\Validation\Between;
use Spatie\LaravelData\Attributes\Validation\Max;
use Spatie\LaravelData\Attributes\Validation\Nullable;

/**
 * Props for a "map" block. `latitude`, `longitude` and `zoom` frame the view;
 * every pin is a {@see MapMarkerData}.
 *
 * `provider` was a free string of which a single value meant anything; it is an
 * enum now, so an unknown provider is a validation error rather than a silent
 * fallback.
 */
class MapData extends PropsData
{
    /**
     * @param  MapMarkerData[]  $markers
     * @param  CtaData[]  $ctas
     */
    public function __construct(
        #[Nullable, Max(255)]
        public ?string $pre = null,
        #[Between(-90, 90)]
        public float $latitude = 0.0,
        #[Between(-180, 180)]
        public float $longitude = 0.0,
        #[Between(0, 22)]
        public int $zoom = 12,
        public MapProvider $provider = MapProvider::OpenStreetMap,
        #[DataCollectionOf(MapMarkerData::class)]
        public array $markers = [],
        #[DataCollectionOf(CtaData::class)]
        public array $ctas = [],
        public MapStylesData $styles = new MapStylesData,
    ) {}
}
