<?php

namespace OiLab\OiLaravelPublish\Support\PropsMigration;

/**
 * What one block's props migration changed, for the command to print.
 */
final class BlockPropsChange
{
    /**
     * @param  array<string, array{mixed, mixed}>  $changes  `path => [before, after]`
     */
    public function __construct(
        public readonly int $blockId,
        public readonly string $templateKey,
        public readonly array $changes,
    ) {}
}
