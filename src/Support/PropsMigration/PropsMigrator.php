<?php

namespace OiLab\OiLaravelPublish\Support\PropsMigration;

use Illuminate\Support\Facades\DB;
use OiLab\OiLaravelPublish\OiLaravelPublish;

/**
 * Walks `publish_blocks` and hands each block's props to a lot.
 *
 * It reads the **raw** JSON column through the query builder rather than the
 * model: `PropsCast` hydrates props into the template's current class, which
 * drops every name the lot is here to rename, before the lot could ever see it.
 * That is the single most important line of this file.
 */
class PropsMigrator
{
    /** @var array<string, PropsLot> */
    private array $lots;

    /**
     * @param  list<PropsLot>  $lots  The lots this migrator knows, in order. Two
     *                                lots never touch the same property, so they
     *                                can be replayed independently.
     */
    public function __construct(array $lots = [])
    {
        $lots = $lots === [] ? [new LotA, new LotB] : $lots;

        $this->lots = array_column(
            array_map(static fn (PropsLot $lot): array => [$lot->key(), $lot], $lots),
            1,
            0,
        );
    }

    /** @return array<string, PropsLot> */
    public function lots(): array
    {
        return $this->lots;
    }

    public function lot(string $key): ?PropsLot
    {
        return $this->lots[strtoupper($key)] ?? null;
    }

    /**
     * Run one lot over every block, in chunks.
     *
     * @param  callable(BlockPropsChange): void|null  $onChange  Called once per block that actually changes.
     * @return array{scanned: int, changed: int}
     */
    public function run(PropsLot $lot, bool $dryRun = true, int $chunk = 200, ?callable $onChange = null): array
    {
        $model = OiLaravelPublish::blockModel();
        $table = (new $model)->getTable();

        $scanned = 0;
        $changed = 0;

        DB::table($table)
            ->select(['id', 'template_key', 'props'])
            ->orderBy('id')
            ->chunkById($chunk, function ($blocks) use ($lot, $dryRun, $table, $onChange, &$scanned, &$changed): void {
                foreach ($blocks as $block) {
                    $scanned++;

                    $before = $this->decode($block->props);
                    $after = $lot->migrate($block->template_key, $before);

                    if ($after === $before) {
                        continue;
                    }

                    $changed++;

                    if ($onChange !== null) {
                        $onChange(new BlockPropsChange(
                            (int) $block->id,
                            $block->template_key,
                            Props::diff($before, $after),
                        ));
                    }

                    if (! $dryRun) {
                        DB::table($table)
                            ->where('id', $block->id)
                            ->update(['props' => json_encode($after, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)]);
                    }
                }
            });

        return ['scanned' => $scanned, 'changed' => $changed];
    }

    /**
     * @return array<string, mixed>
     */
    private function decode(mixed $props): array
    {
        if (is_array($props)) {
            return $props;
        }

        $decoded = is_string($props) ? json_decode($props, true) : null;

        return is_array($decoded) ? $decoded : [];
    }
}
