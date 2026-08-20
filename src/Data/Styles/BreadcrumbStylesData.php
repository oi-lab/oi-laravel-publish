<?php

namespace OiLab\OiLaravelPublish\Data\Styles;

use Spatie\LaravelData\Data;

/**
 * Presentation of a "breadcrumb" block. Navigation carries no calls to action.
 *
 * Slots are composed, never inherited: oi-laravel-ts reads only the constructor
 * of the class it reflects, so an inherited property would vanish from the
 * generated interface without warning.
 */
class BreadcrumbStylesData extends Data
{
    public function __construct(
        public ?BlockStyleData $block = null,
    ) {}
}
