<?php

namespace OiLab\OiLaravelPublish\Data\Blocks;

use OiLab\OiLaravelPublish\Data\PropsData;
use Spatie\LaravelData\Attributes\Validation\Max;
use Spatie\LaravelData\Attributes\Validation\Nullable;

/**
 * Props for a "table" block.
 *
 * @property array<int, string> $headers
 * @property array<int, array<int, string>> $rows
 */
class TableData extends PropsData
{
    /**
     * @param  array<int, string>  $headers
     * @param  array<int, array<int, string>>  $rows
     */
    public function __construct(
        #[Nullable, Max(255)]
        public ?string $caption = null,
        public array $headers = [],
        public array $rows = [],
    ) {}
}
