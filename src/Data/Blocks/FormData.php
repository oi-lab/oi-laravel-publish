<?php

namespace OiLab\OiLaravelPublish\Data\Blocks;

use OiLab\OiLaravelPublish\Data\CtaData;
use OiLab\OiLaravelPublish\Data\PropsData;
use OiLab\OiLaravelPublish\Data\Styles\FormStylesData;
use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Attributes\Validation\In;
use Spatie\LaravelData\Attributes\Validation\Max;
use Spatie\LaravelData\Attributes\Validation\Nullable;
use Spatie\LaravelData\Attributes\Validation\Required;

/**
 * Props for a "form" block. `form_key` references a form handled by the host
 * application; this block only carries presentation and routing hints.
 *
 * `submit_label` is the form's own submit control, not a call to action: it
 * posts the form rather than navigating away. Secondary actions go in `ctas`.
 */
class FormData extends PropsData
{
    /**
     * @param  CtaData[]  $ctas
     */
    public function __construct(
        #[Required, Max(255)]
        public string $form_key,
        #[Nullable, Max(255)]
        public ?string $pre = null,
        #[Nullable, Max(2048)]
        public ?string $action = null,
        #[Nullable, In(['get', 'post'])]
        public ?string $method = 'post',
        #[Nullable, Max(255)]
        public ?string $submit_label = null,
        #[Nullable]
        public ?string $success_message = null,
        #[DataCollectionOf(CtaData::class)]
        public array $ctas = [],
        public FormStylesData $styles = new FormStylesData,
    ) {}
}
