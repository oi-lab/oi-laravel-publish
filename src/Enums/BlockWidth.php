<?php

namespace OiLab\OiLaravelPublish\Enums;

/**
 * How wide a block is: small, medium, large, or full width.
 */
enum BlockWidth: string
{
    case Auto = 'auto';
    case ExtraSmall = 'xs';
    case Small = 'sm';
    case Medium = 'md';
    case Large = 'lg';
    case Full = 'full';
}
