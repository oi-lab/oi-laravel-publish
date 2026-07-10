<?php

namespace OiLab\OiLaravelPublish\Enums;

/**
 * Cross-axis placement of a block's content, mapping to Tailwind's `items-*`.
 */
enum BlockAlignItems: string
{
    case Start = 'start';
    case Center = 'center';
    case End = 'end';
    case Stretch = 'stretch';
}
