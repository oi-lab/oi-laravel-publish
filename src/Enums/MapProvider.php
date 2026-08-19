<?php

namespace OiLab\OiLaravelPublish\Enums;

use OiLab\OiLaravelPublish\Data\Blocks\MapData;

/**
 * Which tile provider a map renders from.
 *
 * It used to be a free string on {@see MapData},
 * of which a single value had any effect: everything else fell back to
 * OpenStreetMap without a word. Typing it makes that fallback a decision rather
 * than an accident.
 */
enum MapProvider: string
{
    case OpenStreetMap = 'openstreetmap';
    case Google = 'google';
}
