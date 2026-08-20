<?php

namespace OiLab\OiLaravelPublish\Enums;

/**
 * The block's outer vertical (top and bottom) margin: small, medium, large, extra large, or none.
 */
enum BlockMarginY: string
{
    case Small = 'sm';
    case Medium = 'md';
    case Large = 'lg';
    case ExtraLarge = 'xl';
    case None = 'none';
}
