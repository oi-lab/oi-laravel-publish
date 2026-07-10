<?php

namespace OiLab\OiLaravelPublish\Enums;

/**
 * The size of a single call-to-action button.
 *
 * Distinct from {@see TextScale}, which sets the typographic scale of a whole
 * group: a `lg` primary button can sit next to an `xs` secondary one inside a
 * group whose scale is `base`.
 */
enum CtaSize: string
{
    case Xs = 'xs';
    case Sm = 'sm';
    case Default = 'default';
    case Lg = 'lg';
}
