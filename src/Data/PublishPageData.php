<?php

namespace OiLab\OiLaravelPublish\Data;

use OiLab\OiLaravelPublish\Models\PublishPage;
use Spatie\LaravelData\Data;

/**
 * PublishPageData
 *
 * Serialisable representation of a {@see PublishPage}. `props` is the raw,
 * flattened props map (from the model's typed PropsData via `toProps()`), so the
 * DTO JSON is uniform regardless of whether the template declares a typed class.
 */
class PublishPageData extends Data
{
    /**
     * @param  array<string, mixed>  $props
     */
    public function __construct(
        public ?int $id,
        public ?string $uuid,
        public ?int $parent_id,
        public string $template_key,
        public string $name,
        public string $slug,
        public ?string $excerpt,
        public ?string $description,
        public array $props,
        public int $sort = 0,
        public bool $is_active = true,
    ) {}
}
