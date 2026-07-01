<?php

namespace OiLab\OiLaravelPublish\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;
use OiLab\OiLaravelPublish\Data\GenericPropsData;
use OiLab\OiLaravelPublish\Data\PropsData;
use OiLab\OiLaravelPublish\OiLaravelPublish;

/**
 * PropsCast
 *
 * Casts the JSON `props` column to a {@see PropsData}. The concrete subclass is
 * resolved from the row's `template_key` through the template registry: when the
 * template declares a typed props class it is hydrated, otherwise the permissive
 * {@see GenericPropsData} bag is returned.
 *
 * The `PropsData` return type keeps this cast discoverable by oi-laravel-ts.
 *
 * @implements CastsAttributes<PropsData, PropsData|array<string, mixed>>
 */
class PropsCast implements CastsAttributes
{
    /**
     * @param  array<string, mixed>  $attributes
     */
    public function get(Model $model, string $key, mixed $value, array $attributes): PropsData
    {
        $raw = $this->decode($value);
        $class = $this->resolvePropsClass($attributes);

        if ($class !== null) {
            return $class::from($raw);
        }

        return GenericPropsData::fromProps($raw);
    }

    /**
     * @param  array<string, mixed>  $attributes
     * @return array<string, string>
     */
    public function set(Model $model, string $key, mixed $value, array $attributes): array
    {
        if ($value instanceof PropsData) {
            $value = $value->toProps();
        } elseif ($value === null) {
            $value = [];
        }

        return [$key => json_encode($value)];
    }

    /**
     * @return array<string, mixed>
     */
    protected function decode(mixed $value): array
    {
        if (is_array($value)) {
            return $value;
        }

        if (is_string($value) && $value !== '') {
            $decoded = json_decode($value, true);

            return is_array($decoded) ? $decoded : [];
        }

        return [];
    }

    /**
     * @param  array<string, mixed>  $attributes
     * @return class-string<PropsData>|null
     */
    protected function resolvePropsClass(array $attributes): ?string
    {
        $templateKey = $attributes['template_key'] ?? null;

        if (! is_string($templateKey)) {
            return null;
        }

        $class = OiLaravelPublish::template($templateKey)?->propsClass;

        if ($class !== null && is_subclass_of($class, PropsData::class)) {
            return $class;
        }

        return null;
    }
}
