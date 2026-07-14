<?php

namespace OiLab\OiLaravelPublish\Enums;

/**
 * How wide a block is: small, medium, large, or none.
 */
enum BlockSpaceY: string
{
    case Small = 'sm';
    case Medium = 'md';
    case Large = 'lg';
    case None = 'none';
}
