<?php

namespace OiLab\OiLaravelPublish\Data\Blocks;

use OiLab\OiLaravelPublish\Data\CtaData;
use OiLab\OiLaravelPublish\Data\PropsData;
use OiLab\OiLaravelPublish\Data\Styles\ContentStylesData;
use OiLab\OiLaravelPublish\Enums\CoverLayout;
use OiLab\OiLaravelPublish\Enums\MediaRatio;
use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Attributes\Validation\In;
use Spatie\LaravelData\Attributes\Validation\Max;
use Spatie\LaravelData\Attributes\Validation\Nullable;

/**
 * Props for a free-form "content" block.
 *
 * The body itself is the block's `description` column; `format` only tells the
 * host which renderer to hand it to. An optional `cover` attachment is arranged
 * with `cover_layout`, and `pre` is the kicker shown above the title.
 */
class ContentData extends PropsData
{
    /**
     * @param  CtaData[]  $ctas
     */
    public function __construct(
        #[Nullable, Max(255)]
        public ?string $pre = null,
        #[Nullable, In(['markdown', 'html'])]
        public ?string $format = 'markdown',
        public CoverLayout $cover_layout = CoverLayout::Right,
        public MediaRatio $cover_ratio = MediaRatio::Inherit,
        #[DataCollectionOf(CtaData::class)]
        public array $ctas = [],
        public ContentStylesData $styles = new ContentStylesData,
    ) {}
}
