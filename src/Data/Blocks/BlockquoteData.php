<?php

namespace OiLab\OiLaravelPublish\Data\Blocks;

use OiLab\OiLaravelPublish\Data\CtaData;
use OiLab\OiLaravelPublish\Data\PropsData;
use OiLab\OiLaravelPublish\Data\Styles\BlockquoteStylesData;
use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Attributes\Validation\Max;
use Spatie\LaravelData\Attributes\Validation\Nullable;

/**
 * Props for a "blockquote" block.
 *
 * The quoted text is the block's `description` column; these props only carry
 * its attribution.
 */
class BlockquoteData extends PropsData
{
    /**
     * @param  CtaData[]  $ctas
     */
    public function __construct(
        #[Nullable, Max(255)]
        public ?string $author = null,
        #[Nullable, Max(255)]
        public ?string $role = null,
        #[Nullable, Max(2048)]
        public ?string $source_url = null,
        #[DataCollectionOf(CtaData::class)]
        public array $ctas = [],
        public BlockquoteStylesData $styles = new BlockquoteStylesData,
    ) {}
}
