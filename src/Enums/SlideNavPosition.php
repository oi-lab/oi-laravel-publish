<?php

namespace OiLab\OiLaravelPublish\Enums;

/**
 * Where the navigation for a "slides" block is positioned: top, bottom, or inherited from the theme.
 */
enum SlideNavPosition: string
{
    case Inherit = 'inherit';
    case Top = 'top';
    case Bottom = 'bottom';
}
