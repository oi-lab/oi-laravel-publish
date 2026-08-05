<?php

namespace OiLab\OiLaravelPublish\Data\Pages;

use OiLab\OiLaravelPublish\Data\ParamData;
use OiLab\OiLaravelPublish\Data\PropsData;
use Spatie\LaravelData\Attributes\DataCollectionOf;

/**
 * PagePropsData
 *
 * The typed props of a page. Where a block's props describe how one template
 * renders, a page's props describe the page as a whole — which is the same
 * concern whatever the page template — so the bundled page templates all point
 * their `propsClass` at this single class rather than one class per template.
 *
 * `params` is an ordered list of free-form {@see ParamData} key/value pairs: the
 * escape hatch a page needs for what a project alone knows about (a tracking id,
 * a template variant, an external reference), without the package having to
 * guess a field for it. Read one with {@see param()}.
 */
class PagePropsData extends PropsData
{
    /**
     * @param  ParamData[]  $params
     */
    public function __construct(
        #[DataCollectionOf(ParamData::class)]
        public array $params = [],
    ) {}

    /**
     * The value of the param named `$key`.
     *
     * A missing param and a param holding null both read as `$default`; use
     * {@see hasParam()} when that distinction matters.
     */
    public function param(string $key, ?string $default = null): ?string
    {
        return $this->paramsMap()[$key] ?? $default;
    }

    public function hasParam(string $key): bool
    {
        return array_key_exists($key, $this->paramsMap());
    }

    /**
     * The params flattened to a `key => value` map, last occurrence winning.
     *
     * @return array<string, string|null>
     */
    public function paramsMap(): array
    {
        return ParamData::map($this->params);
    }
}
