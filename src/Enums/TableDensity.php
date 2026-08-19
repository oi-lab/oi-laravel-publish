<?php

namespace OiLab\OiLaravelPublish\Enums;

/**
 * How tightly a table packs its cells. `Default` is the rhythm the theme was
 * drawn around; the other two step away from it in either direction.
 */
enum TableDensity: string
{
    case Compact = 'compact';
    case Default = 'default';
    case Comfortable = 'comfortable';
}
