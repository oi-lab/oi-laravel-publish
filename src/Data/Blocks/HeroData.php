<?php

namespace OiLab\OiLaravelPublish\Data\Blocks;

use OiLab\OiLaravelPublish\Data\CtaData;
use OiLab\OiLaravelPublish\Data\PropsData;
use OiLab\OiLaravelPublish\Data\Styles\HeroStylesData;
use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Attributes\Validation\Max;
use Spatie\LaravelData\Attributes\Validation\Nullable;

/**
 * Props for a "hero" block.
 *
 * The title, lead and body come from the block's `name`, `excerpt` and
 * `description` columns — props never duplicate them. The background image is
 * supplied through the block's `cover` attachment collection.
 */
class HeroData extends PropsData
{
    /**
     * @param  CtaData[]  $ctas
     */
    public function __construct(
        #[Nullable, Max(255)]
        public ?string $pre = null,
        #[DataCollectionOf(CtaData::class)]
        public array $ctas = [],
        public HeroStylesData $styles = new HeroStylesData,
    ) {}
}
