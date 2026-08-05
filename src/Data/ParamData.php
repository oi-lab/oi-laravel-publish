<?php

namespace OiLab\OiLaravelPublish\Data;

use Spatie\LaravelData\Attributes\Validation\Max;
use Spatie\LaravelData\Attributes\Validation\Nullable;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Data;

/**
 * ParamData
 *
 * A single free-form `key` / `value` pair. Pages carry an ordered list of them
 * in their props, so a project can hang its own switches on a page — a tracking
 * id, a template variant, an external reference — without a migration or a new
 * typed field per need.
 *
 * `value` is a nullable string on purpose: params are edited as text in the
 * console, and the host casts to whatever it needs when it reads one.
 */
class ParamData extends Data
{
    public function __construct(
        #[Required, Max(255)]
        public string $key,
        #[Nullable, Max(2048)]
        public ?string $value = null,
    ) {}

    /**
     * Flatten a param list into a `key => value` map, last occurrence winning.
     *
     * Both the hydrated (`ParamData`) and the raw (`['key' => …, 'value' => …]`)
     * shapes are accepted, so a caller can look a param up whether the props
     * were typed or left in the generic bag. Non-scalar values read as null.
     *
     * @param  array<int, mixed>  $params
     * @return array<string, string|null>
     */
    public static function map(array $params): array
    {
        $map = [];

        foreach ($params as $param) {
            if ($param instanceof self) {
                $map[$param->key] = $param->value;

                continue;
            }

            if (! is_array($param) || ! isset($param['key']) || ! is_string($param['key'])) {
                continue;
            }

            $value = $param['value'] ?? null;

            $map[$param['key']] = is_scalar($value) ? (string) $value : null;
        }

        return $map;
    }
}
