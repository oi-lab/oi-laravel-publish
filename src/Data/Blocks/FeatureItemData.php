<?php

namespace OiLab\OiLaravelPublish\Data\Blocks;

use OiLab\OiLaravelPublish\Data\CtaData;
use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Attributes\Validation\Max;
use Spatie\LaravelData\Attributes\Validation\Nullable;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Data;

/**
 * A single item inside a "features" block.
 */
class FeatureItemData extends Data
{
    /**
     * @param  CtaData[]  $ctas
     */
    public function __construct(
        #[Required, Max(255)]
        public string $title,
        #[Nullable]
        public ?string $text = null,
        #[Nullable, Max(255)]
        public ?string $icon = null,
        #[DataCollectionOf(CtaData::class)]
        public array $ctas = [],
    ) {}
}
