<?php

namespace OiLab\OiLaravelPublish\Data\Styles;

use Spatie\LaravelData\Attributes\Validation\Between;
use Spatie\LaravelData\Attributes\Validation\Nullable;
use Spatie\LaravelData\Data;

/**
 * BreakpointsData
 *
 * A responsive integer: how many columns a grid shows, how many slides a
 * carousel fits. `base` always applies; each breakpoint overrides it upwards,
 * and a null one inherits from the breakpoint below.
 *
 * `xxl` maps to Tailwind's `2xl` — a PHP property cannot start with a digit.
 */
class BreakpointsData extends Data
{
    public function __construct(
        #[Between(1, 12)]
        public int $base = 1,
        #[Nullable, Between(1, 12)]
        public ?int $sm = null,
        #[Nullable, Between(1, 12)]
        public ?int $md = null,
        #[Nullable, Between(1, 12)]
        public ?int $lg = null,
        #[Nullable, Between(1, 12)]
        public ?int $xl = null,
        #[Nullable, Between(1, 12)]
        public ?int $xxl = null,
    ) {}
}
