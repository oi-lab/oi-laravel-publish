<?php

namespace OiLab\OiLaravelPublish\Data;

/**
 * GenericPropsData
 *
 * The permissive fallback used when a template declares no typed props class.
 * It preserves any JSON shape under {@see $props} and round-trips it untouched.
 */
class GenericPropsData extends PropsData
{
    /**
     * @var array<string, mixed>
     */
    public array $props = [];

    /**
     * @param  array<string, mixed>  $props
     */
    public function __construct(array $props = [])
    {
        $this->props = $props;
    }

    /**
     * @param  array<string, mixed>  $props
     */
    public static function fromProps(array $props): static
    {
        return new static($props);
    }

    /**
     * @return array<string, mixed>
     */
    public function toProps(): array
    {
        return $this->props;
    }

    public function value(string $key, mixed $default = null): mixed
    {
        return $this->props[$key] ?? $default;
    }
}
