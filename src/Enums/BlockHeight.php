<?php

namespace OiLab\OiLaravelPublish\Enums;

/**
 * How tall a block is: as tall as its content, or a full viewport (`h-screen`).
 */
enum BlockHeight: string
{
    case Inherit = 'inherit';
    case Screen = 'screen';
}
