<?php

namespace OiLab\OiLaravelPublish\Data\Items;

use Spatie\LaravelData\Attributes\Validation\Max;
use Spatie\LaravelData\Attributes\Validation\Nullable;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Data;

/**
 * A single item inside a "warranty" block.
 *
 * The simplest of the catalogue: a title, a body, an icon. No image, no calls to
 * action, no `media` — so its card in the console shows three fields and no
 * media group at all.
 */
class WarrantyItemData extends Data
{
    public function __construct(
        #[Required, Max(255)]
        public string $title,
        #[Nullable]
        public ?string $text = null,
        #[Nullable, Max(255)]
        public ?string $icon = null,
    ) {}
}
