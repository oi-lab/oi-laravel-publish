<?php

namespace OiLab\OiLaravelPublish\Data\Styles;

use OiLab\OiLaravelPublish\Enums\TableBorders;
use OiLab\OiLaravelPublish\Enums\TableDensity;
use Spatie\LaravelData\Data;

/**
 * Presentation of the grid a "table" block draws: how tightly it packs its
 * cells, which rules it draws, and whether its rows alternate shade.
 *
 * The defaults reproduce the table as it was written before it was a choice —
 * a rule under each row, no zebra, the theme's own rhythm.
 */
class TableStyleData extends Data
{
    public function __construct(
        public TableDensity $density = TableDensity::Default,
        public TableBorders $borders = TableBorders::Rows,
        public bool $striped = false,
    ) {}
}
