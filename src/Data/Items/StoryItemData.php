<?php

namespace OiLab\OiLaravelPublish\Data\Items;

use OiLab\OiLaravelPublish\Data\CtaData;
use OiLab\OiLaravelPublish\Data\Styles\ItemMediaStyleData;
use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Attributes\Validation\Max;
use Spatie\LaravelData\Attributes\Validation\Nullable;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Data;

/**
 * A single step inside a "story" block.
 *
 * Structurally identical to {@see GridItemData} — same vocabulary, same
 * order, `ctas` included, which a step used to be denied. The two are kept
 * apart so they *can* diverge, not because they do: what differs is the
 * component that draws them, a rail against a grid.
 *
 * A step's `pre` is its date, rendered on the rail rather than above its title
 * (rule S7). That is a decision of the component, passed to `BlockItemText` as
 * an explicit flag.
 */
class StoryItemData extends Data
{
    /**
     * @param  CtaData[]  $ctas
     */
    public function __construct(
        #[Nullable, Max(255)]
        public ?string $pre = null,
        #[Required, Max(255)]
        public string $title = '',
        #[Nullable]
        public ?string $text = null,
        #[Nullable, Max(255)]
        public ?string $icon = null,
        #[Nullable, Max(36)]
        public ?string $attachment_uuid = null,
        #[DataCollectionOf(CtaData::class)]
        public array $ctas = [],
        public ItemMediaStyleData $media = new ItemMediaStyleData,
    ) {}
}
