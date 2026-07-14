<?php

namespace OiLab\OiLaravelPublish\Enums;

/**
 * How horizontal margin is applied: left, auto, right, or none.
 */
enum BlockMarginX: string
{
    case Left = 'left';
    case Auto = 'auto';
    case Right = 'right';
    case None = 'none';
}
