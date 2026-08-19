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
 * A single item inside a "grid" block.
 *
 * It carries the closed vocabulary of a repeated element, in its fixed order:
 * `pre`, `title`, `text`, `icon`, `attachment_uuid`, `ctas`, `media`. Six
 * elements describe the same thing across the catalogue, so they say it with
 * the same words.
 *
 * `attachment_uuid` links the item to one entry of the block's `gallery`
 * collection by that attachment's stable `uuid`; it is null for an item with no
 * image. The host resolves the image by uuid (not by position) and enforces that
 * the uuid belongs to the block.
 *
 * `title` carries an empty default so the vocabulary's order can be honoured —
 * PHP deprecates an optional parameter declared before a required one. It stays
 * mandatory where it matters: `Required` rejects an empty string at validation.
 */
class GridItemData extends Data
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
