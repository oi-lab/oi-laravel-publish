<?php

namespace OiLab\OiLaravelPublish\Data\Items;

use OiLab\OiLaravelPublish\Data\CtaData;
use OiLab\OiLaravelPublish\Data\Styles\ItemMediaStyleData;
use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Attributes\Validation\Max;
use Spatie\LaravelData\Attributes\Validation\Nullable;
use Spatie\LaravelData\Data;

/**
 * One slide of a "slides" block.
 *
 * A slide is potentially text (`title`, `text`), potentially one image, and
 * potentially a link. It carries no `pre` and no `icon`: a slide has neither.
 *
 * `text` was called `caption` — a word for the same thing that kept slides out
 * of the markdown circuit, since the renderer looks for `text`. `ctas` was a
 * single nullable object, so a slide could offer exactly one action and no
 * shared editor could touch it; it is a collection like everywhere else. A
 * slide still has no slot to place an action in, so its CTAs keep a null
 * `position` and fall in after the text.
 *
 * `attachment_uuid` links the slide to one entry of the block's `slides`
 * collection by that attachment's stable `uuid`; it is null for a text-only
 * slide. The host resolves the image by uuid (not by position) and enforces that
 * the uuid belongs to the block.
 */
class SlideItemData extends Data
{
    /**
     * @param  CtaData[]  $ctas
     */
    public function __construct(
        #[Nullable, Max(255)]
        public ?string $title = null,
        #[Nullable]
        public ?string $text = null,
        #[Nullable, Max(36)]
        public ?string $attachment_uuid = null,
        #[DataCollectionOf(CtaData::class)]
        public array $ctas = [],
        public ItemMediaStyleData $media = new ItemMediaStyleData,
    ) {}
}
