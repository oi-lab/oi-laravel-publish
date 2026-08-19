<?php

namespace OiLab\OiLaravelPublish\Enums;

/**
 * Which rules a table draws: none at all, one under each row, or a full grid.
 */
enum TableBorders: string
{
    case None = 'none';
    case Rows = 'rows';
    case Grid = 'grid';
}
