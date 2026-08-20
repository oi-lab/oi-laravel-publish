<?php

namespace OiLab\OiLaravelPublish\Enums;

/**
 * The horizontal gap between a block's content and its media when they sit
 * side by side: extra small, small, medium, large, extra large, or none.
 */
enum BlockSpaceX: string
{
    case ExtraSmall = 'xs';
    case Small = 'sm';
    case Medium = 'md';
    case Large = 'lg';
    case ExtraLarge = 'xl';
    case None = 'none';
}
