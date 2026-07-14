<?php

namespace OiLab\OiLaravelPublish\Data\Blocks;

use OiLab\OiLaravelPublish\Data\CtaData;
use OiLab\OiLaravelPublish\Enums\ItemLayout;
use OiLab\OiLaravelPublish\Enums\MediaRatio;
use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Attributes\Validation\Max;
use Spatie\LaravelData\Attributes\Validation\Nullable;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Data;

/**
 * A single item inside a "features" block.
 *
 * An item is potentially an eyebrow (`pre`) above its `title`, body `text`, an
 * `icon`, an optional cover image, and calls to action. `attachment_uuid` links
 * the item to one entry of the block's `gallery` attachment collection by that
 * attachment's stable `uuid`; it is null for an item with no cover. The host
 * resolves the image by uuid (not by position) and enforces that the uuid
 * belongs to the block. `item_layout` arranges that cover relative to the text,
 * `max_width` optionally constrains the item's width, and `cover_ratio` is the
 * item's aspect ratio; `MediaRatio::Inherit` defers to the block's `cover_ratio`,
 * then the theme.
 */
class FeatureItemData extends Data
{
    /**
     * @param  CtaData[]  $ctas
     */
    public function __construct(
        #[Required, Max(255)]
        public string $title,
        #[Nullable, Max(255)]
        public ?string $pre = null,
        #[Nullable]
        public ?string $text = null,
        #[Nullable, Max(255)]
        public ?string $icon = null,
        #[Nullable, Max(36)]
        public ?string $attachment_uuid = null,
        public ItemLayout $item_layout = ItemLayout::Left,
        #[Nullable, Max(32)]
        public ?string $max_width = null,
        public MediaRatio $cover_ratio = MediaRatio::Inherit,
        #[DataCollectionOf(CtaData::class)]
        public array $ctas = [],
    ) {}
}
