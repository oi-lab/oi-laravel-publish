<?php

namespace OiLab\OiLaravelPublish\Support\PropsMigration;

/**
 * One lot of props transformations.
 *
 * Props live as JSON in `publish_blocks.props`, so renaming one is a data
 * operation rather than a code one. A lot gathers the renames of a single phase:
 * they never overlap, so lots can be replayed independently and in any order.
 *
 * Every lot must be **idempotent**: a block already in the target shape is
 * handed back untouched, and the migrator writes nothing for it.
 */
interface PropsLot
{
    /** The `--lot=` value that selects it. */
    public function key(): string;

    /** One line, shown by the command. */
    public function description(): string;

    /**
     * The block's props, transformed. Same array in, same array out when there
     * is nothing to do.
     *
     * @param  array<string, mixed>  $props  The raw decoded JSON, never hydrated
     *                                       through PropsCast: the cast would
     *                                       drop the old names before they could
     *                                       be read.
     * @return array<string, mixed>
     */
    public function migrate(string $templateKey, array $props): array;
}
