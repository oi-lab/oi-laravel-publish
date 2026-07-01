<?php

namespace OiLab\OiLaravelPublish\Data\Blocks;

use OiLab\OiLaravelPublish\Data\PropsData;
use Spatie\LaravelData\Attributes\Validation\In;
use Spatie\LaravelData\Attributes\Validation\Max;
use Spatie\LaravelData\Attributes\Validation\Nullable;
use Spatie\LaravelData\Attributes\Validation\Required;

/**
 * Props for a "form" block. `form_key` references a form handled by the host
 * application; this block only carries presentation and routing hints.
 */
class FormData extends PropsData
{
    public function __construct(
        #[Required, Max(255)]
        public string $form_key,
        #[Nullable, Max(2048)]
        public ?string $action = null,
        #[Nullable, In(['get', 'post'])]
        public ?string $method = 'post',
        #[Nullable, Max(255)]
        public ?string $submit_label = null,
        #[Nullable]
        public ?string $success_message = null,
    ) {}
}
