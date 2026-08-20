<?php

namespace OiLab\OiLaravelPublish\Enums;

/**
 * Main-axis placement of a block's content, mapping to Tailwind's `justify-*`.
 */
enum BlockJustify: string
{
    case Start = 'start';
    case Center = 'center';
    case End = 'end';
    case Between = 'between';
    case Around = 'around';
}
