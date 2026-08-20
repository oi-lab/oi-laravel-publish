<?php

namespace OiLab\OiLaravelPublish\Enums;

/**
 * The block's inner vertical (top and bottom) padding: small, medium, large, extra large, or none.
 */
enum BlockPaddingY: string
{
    case Small = 'sm';
    case Medium = 'md';
    case Large = 'lg';
    case ExtraLarge = 'xl';
    case None = 'none';
}
