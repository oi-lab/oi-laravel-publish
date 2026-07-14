<?php

namespace OiLab\OiLaravelPublish\Enums;

/**
 * A typographic scale applied to a whole element — a quote, a group of calls to
 * action. Distinct from {@see CtaSize}, which sizes one button.
 */
enum TextScale: string
{
    case Xs = 'xs';
    case Sm = 'sm';
    case Base = 'base';
    case Lg = 'lg';
    case Xl = 'xl';
    case Xxl = '2xl';
    case Xxxl = '3xl';
    case Inherit = 'inherit';
}
