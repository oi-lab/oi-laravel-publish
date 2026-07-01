<?php

namespace OiLab\OiLaravelPublish\Data;

use OiLab\OiLaravelPublish\Casts\PropsCast;
use Spatie\LaravelData\Data;

/**
 * PropsData
 *
 * Abstract base for the typed `props` of a page or block. Every concrete props
 * class — the generic {@see GenericPropsData} bag and the typed block props
 * (HeroData, FeaturesData, ...) — extends this class, so the
 * {@see PropsCast} can declare a single
 * `PropsData` return type. That keeps the cast discoverable by oi-laravel-ts
 * while each subclass still serialises its own fields.
 */
abstract class PropsData extends Data
{
    /**
     * The raw array to persist back into the JSON `props` column.
     *
     * Typed subclasses serialise their declared fields through spatie's native
     * array transformer; the generic bag overrides this to return its raw map.
     *
     * @return array<string, mixed>
     */
    public function toProps(): array
    {
        return $this->toArray();
    }
}
