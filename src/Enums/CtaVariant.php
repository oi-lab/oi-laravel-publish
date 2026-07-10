<?php

namespace OiLab\OiLaravelPublish\Enums;

/**
 * The visual weight of a call to action, mirroring the host button variants.
 */
enum CtaVariant: string
{
    case Default = 'default';
    case Secondary = 'secondary';
    case Ghost = 'ghost';
    case Link = 'link';
}
