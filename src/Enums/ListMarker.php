<?php

namespace OiLab\OiLaravelPublish\Enums;

/**
 * The decoration in front of each list item. `Svg` defers to the accompanying
 * `marker_icon`, a file under `public/images/markers/`.
 */
enum ListMarker: string
{
    case Disc = 'disc';
    case Dash = 'dash';
    case Svg = 'svg';
}
