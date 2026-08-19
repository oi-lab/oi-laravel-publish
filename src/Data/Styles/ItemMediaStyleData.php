<?php

namespace OiLab\OiLaravelPublish\Data\Styles;

use OiLab\OiLaravelPublish\Enums\ItemLayout;
use OiLab\OiLaravelPublish\Enums\MediaRatio;
use Spatie\LaravelData\Attributes\Validation\Max;
use Spatie\LaravelData\Attributes\Validation\Nullable;
use Spatie\LaravelData\Data;

/**
 * Presentation of one repeated element's own image: where it sits against the
 * element's text, the shape it is shown at, and how wide the element may grow.
 *
 * {@see ItemLayout} has no `background`: an element does not put its image
 * behind itself. `max_width` is a free CSS length, so it rides through to the
 * browser as an inline cap rather than as a class.
 */
class ItemMediaStyleData extends Data
{
    public function __construct(
        public ItemLayout $layout = ItemLayout::Left,
        public MediaRatio $ratio = MediaRatio::Inherit,
        #[Nullable, Max(32)]
        public ?string $max_width = null,
    ) {}
}
