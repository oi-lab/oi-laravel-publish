<?php

namespace OiLab\OiLaravelPublish\Support\PropsMigration;

/**
 * Reading and writing nested keys of a raw props array.
 *
 * `Arr::get`/`Arr::set` would do most of this, but not `pull` — moving a key is
 * the whole job here, and doing it by hand keeps the "was it there at all?"
 * question answerable, which is what makes a lot idempotent.
 */
final class Props
{
    /**
     * Whether the path exists, however null its value.
     *
     * @param  array<string, mixed>  $props
     */
    public static function has(array $props, string $path): bool
    {
        $cursor = $props;

        foreach (explode('.', $path) as $segment) {
            if (! is_array($cursor) || ! array_key_exists($segment, $cursor)) {
                return false;
            }

            $cursor = $cursor[$segment];
        }

        return true;
    }

    /**
     * @param  array<string, mixed>  $props
     */
    public static function get(array $props, string $path): mixed
    {
        $cursor = $props;

        foreach (explode('.', $path) as $segment) {
            if (! is_array($cursor) || ! array_key_exists($segment, $cursor)) {
                return null;
            }

            $cursor = $cursor[$segment];
        }

        return $cursor;
    }

    /**
     * @param  array<string, mixed>  $props
     * @return array<string, mixed>
     */
    public static function set(array $props, string $path, mixed $value): array
    {
        $segments = explode('.', $path);
        $cursor = &$props;

        foreach ($segments as $segment) {
            if (! isset($cursor[$segment]) || ! is_array($cursor[$segment])) {
                $cursor[$segment] = [];
            }

            $cursor = &$cursor[$segment];
        }

        $cursor = $value;

        return $props;
    }

    /**
     * @param  array<string, mixed>  $props
     * @return array<string, mixed>
     */
    public static function forget(array $props, string $path): array
    {
        $segments = explode('.', $path);
        $last = array_pop($segments);
        $cursor = &$props;

        foreach ($segments as $segment) {
            if (! isset($cursor[$segment]) || ! is_array($cursor[$segment])) {
                return $props;
            }

            $cursor = &$cursor[$segment];
        }

        unset($cursor[$last]);

        return $props;
    }

    /**
     * Move `$from` to `$to`, unless `$to` already holds something — a lot has to
     * survive being replayed, and the target is the newer of the two.
     *
     * @param  array<string, mixed>  $props
     * @return array<string, mixed>
     */
    public static function move(array $props, string $from, string $to, ?callable $transform = null): array
    {
        if (! self::has($props, $from)) {
            return $props;
        }

        if (! self::has($props, $to)) {
            $value = self::get($props, $from);

            $props = self::set($props, $to, $transform === null ? $value : $transform($value));
        }

        return self::forget($props, $from);
    }

    /** Whether a value is a keyed array, as opposed to a list or a scalar. */
    private static function isTree(mixed $value): bool
    {
        return is_array($value) && $value !== [] && ! array_is_list($value);
    }

    /**
     * The paths at which two props arrays differ, as `path => [before, after]`.
     *
     * @param  array<string, mixed>  $before
     * @param  array<string, mixed>  $after
     * @return array<string, array{mixed, mixed}>
     */
    public static function diff(array $before, array $after, string $prefix = ''): array
    {
        $changes = [];

        foreach (array_unique([...array_keys($before), ...array_keys($after)]) as $key) {
            $path = $prefix === '' ? (string) $key : "{$prefix}.{$key}";
            $left = $before[$key] ?? null;
            $right = $after[$key] ?? null;

            if ($left === $right && array_key_exists($key, $before) === array_key_exists($key, $after)) {
                continue;
            }

            // A whole slot appearing at once still reports the leaves it puts
            // there: `styles.block.width`, not `styles.block`. A list is
            // reported whole — its identity is positional, and half a reordered
            // list is unreadable.
            if (self::isTree($left) || self::isTree($right)) {
                $changes = [...$changes, ...self::diff(
                    is_array($left) ? $left : [],
                    is_array($right) ? $right : [],
                    $path,
                )];

                continue;
            }

            $changes[$path] = [$left, $right];
        }

        return $changes;
    }
}
