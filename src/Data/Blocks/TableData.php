<?php

namespace OiLab\OiLaravelPublish\Data\Blocks;

use OiLab\OiLaravelPublish\Data\CtaData;
use OiLab\OiLaravelPublish\Data\PropsData;
use OiLab\OiLaravelPublish\Data\Styles\TableStylesData;
use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Attributes\Validation\Max;
use Spatie\LaravelData\Attributes\Validation\Nullable;

/**
 * Props for a "table" block.
 *
 * The caption is the block's `name` column. `headers` and `rows` keep their
 * domain names — renaming them would obscure them rather than unify anything.
 *
 * @property array<int, string> $headers
 * @property array<int, array<int, string>> $rows
 */
class TableData extends PropsData
{
    /**
     * @param  array<int, string>  $headers
     * @param  array<int, array<int, string>>  $rows
     * @param  CtaData[]  $ctas
     */
    public function __construct(
        #[Nullable, Max(255)]
        public ?string $pre = null,
        public array $headers = [],
        public array $rows = [],
        #[DataCollectionOf(CtaData::class)]
        public array $ctas = [],
        public TableStylesData $styles = new TableStylesData,
    ) {}
}
