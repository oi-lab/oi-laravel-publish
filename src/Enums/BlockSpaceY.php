<?php

namespace OiLab\OiLaravelPublish\Enums;

/**
 * The vertical gap between a block's stacked children: small, medium, large, or none.
 */
enum BlockSpaceY: string
{
    case Small = 'sm';
    case Medium = 'md';
    case Large = 'lg';
    case None = 'none';
}
