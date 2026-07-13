<?php

namespace OiLab\OiLaravelPublish\Data\Blocks;

use OiLab\OiLaravelPublish\Data\PropsData;
use OiLab\OiLaravelPublish\Data\Styles\FaqsStylesData;
use Spatie\LaravelData\Attributes\DataCollectionOf;

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
     */
    public function __construct(
        #[DataCollectionOf(FaqItemData::class)]
        public array $items = [],
        public FaqsStylesData $styles = new FaqsStylesData,
    ) {}
}
