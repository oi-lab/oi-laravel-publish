<?php

namespace OiLab\OiLaravelPublish\Console\Commands;

use Illuminate\Console\Command;
use OiLab\OiLaravelPublish\Support\PropsMigration\BlockPropsChange;
use OiLab\OiLaravelPublish\Support\PropsMigration\PropsMigrator;

/**
 * Renames and conversions of the stored `props` JSON, by lot.
 *
 * Props are data, not code: renaming a property in a `PropsData` class leaves
 * every stored block behind. Worse, spatie ignores a property it does not know
 * without a word but fills a property it *expects* and cannot find with its
 * default — so a forgotten rename does not fail, it quietly blanks content.
 *
 * The command is therefore safe by default (`--dry-run` prints the diff and
 * writes nothing) and idempotent (a block already in the target shape is not
 * touched).
 */
class MigratePropsCommand extends Command
{
    protected $signature = 'publish:migrate-props
        {--lot= : Which lot to run, e.g. A. Omitted, the command lists them.}
        {--dry-run : Print the changes without writing them}
        {--chunk=200 : How many blocks to read at a time}';

    protected $description = 'Migrate the stored props of publish blocks, one lot of renames at a time';

    public function handle(PropsMigrator $migrator): int
    {
        $key = $this->option('lot');

        if (! is_string($key) || $key === '') {
            $this->line('Available lots:');

            foreach ($migrator->lots() as $lot) {
                $this->line("  <info>{$lot->key()}</info>  {$lot->description()}");
            }

            $this->newLine();
            $this->line('Run one with <comment>--lot=A --dry-run</comment>, read the diff, then drop --dry-run.');

            return self::SUCCESS;
        }

        $lot = $migrator->lot($key);

        if ($lot === null) {
            $this->error("No such lot: {$key}.");

            return self::FAILURE;
        }

        $dryRun = (bool) $this->option('dry-run');
        $chunk = max(1, (int) $this->option('chunk'));

        $this->line("Lot <info>{$lot->key()}</info> — {$lot->description()}");
        $this->line($dryRun ? 'Dry run: nothing will be written.' : 'Writing changes.');
        $this->newLine();

        $result = $migrator->run(
            $lot,
            $dryRun,
            $chunk,
            fn (BlockPropsChange $change) => $this->report($change),
        );

        $this->newLine();
        $this->info(sprintf(
            '%d block(s) scanned, %d %s.',
            $result['scanned'],
            $result['changed'],
            $dryRun ? 'would change' : 'changed',
        ));

        return self::SUCCESS;
    }

    private function report(BlockPropsChange $change): void
    {
        $this->line("<comment>#{$change->blockId}</comment> {$change->templateKey}");

        foreach ($change->changes as $path => [$before, $after]) {
            $this->line(sprintf(
                '    %s: %s → %s',
                $path,
                $this->render($before),
                $this->render($after),
            ));
        }
    }

    private function render(mixed $value): string
    {
        return match (true) {
            $value === null => '—',
            is_bool($value) => $value ? 'true' : 'false',
            is_array($value) => json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: '[…]',
            default => (string) $value,
        };
    }
}
