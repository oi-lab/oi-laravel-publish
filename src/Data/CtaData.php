<?php

namespace OiLab\OiLaravelPublish\Data;

use OiLab\OiLaravelPublish\Enums\CtaPosition;
use OiLab\OiLaravelPublish\Enums\CtaSize;
use OiLab\OiLaravelPublish\Enums\CtaTarget;
use OiLab\OiLaravelPublish\Enums\CtaVariant;
use Spatie\LaravelData\Attributes\Validation\Max;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Data;

/**
 * CtaData
 *
 * A single call to action. Blocks carry a `ctas` collection of them rather than
 * a flat `cta_label` / `cta_url` pair, so a block can offer several actions and
 * each can be styled and placed independently.
 *
 * `position` is nullable on purpose: a slide has exactly one CTA and no slot to
 * place it in, so it leaves the decision to the component. Block-level CTAs set
 * it explicitly.
 */
class CtaData extends Data
{
    public function __construct(
        #[Required, Max(255)]
        public string $label,
        #[Required, Max(2048)]
        public string $url,
        public CtaTarget $target = CtaTarget::Self,
        public CtaVariant $variant = CtaVariant::Default,
        public CtaSize $size = CtaSize::Default,
        public ?CtaPosition $position = null,
    ) {}
}
