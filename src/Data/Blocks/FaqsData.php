<?php

namespace OiLab\OiLaravelPublish\Data\Blocks;

use OiLab\OiLaravelPublish\Data\CtaData;
use OiLab\OiLaravelPublish\Data\Items\FaqItemData;
use OiLab\OiLaravelPublish\Data\PropsData;
use OiLab\OiLaravelPublish\Data\Styles\FaqsStylesData;
use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Attributes\Validation\Max;
use Spatie\LaravelData\Attributes\Validation\Nullable;

/**
 * Props for a "faqs" block: a list of question/answer pairs.
 *
 * The title and lead come from the block's `name` and `excerpt` columns; each
 * item carries its own markdown answer.
 */
class FaqsData extends PropsData
{
    /**
     * @param  FaqItemData[]  $items
     * @param  CtaData[]  $ctas
     */
    public function __construct(
        #[Nullable, Max(255)]
        public ?string $pre = null,
        #[DataCollectionOf(FaqItemData::class)]
        public array $items = [],
        #[DataCollectionOf(CtaData::class)]
        public array $ctas = [],
        public FaqsStylesData $styles = new FaqsStylesData,
    ) {}
}
