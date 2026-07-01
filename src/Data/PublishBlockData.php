<?php

namespace OiLab\OiLaravelPublish\Data;

use OiLab\OiLaravelPublish\Data\Blocks\BlockquoteData;
use OiLab\OiLaravelPublish\Data\Blocks\BreadcrumbData;
use OiLab\OiLaravelPublish\Data\Blocks\ContentData;
use OiLab\OiLaravelPublish\Data\Blocks\FeaturesData;
use OiLab\OiLaravelPublish\Data\Blocks\FormData;
use OiLab\OiLaravelPublish\Data\Blocks\HeroData;
use OiLab\OiLaravelPublish\Data\Blocks\MapData;
use OiLab\OiLaravelPublish\Data\Blocks\SlidesData;
use OiLab\OiLaravelPublish\Data\Blocks\TableData;
use OiLab\OiLaravelPublish\Models\PublishBlock;
use Spatie\LaravelData\Data;

/**
 * PublishBlockData
 *
 * Serialisable representation of a {@see PublishBlock}. `props` is the raw,
 * flattened props map (from the model's typed PropsData via `toProps()`), so the
 * DTO JSON is uniform. Its shape conforms to the block template's typed props —
 * the `@param` union below makes oi-laravel-ts emit `props` as the union of the
 * typed block interfaces (`IHeroData | IFeaturesData | ...`).
 */
class PublishBlockData extends Data
{
    /**
     * @param  HeroData|FeaturesData|BlockquoteData|ContentData|FormData|SlidesData|BreadcrumbData|MapData|TableData|array<string, mixed>  $props
     */
    public function __construct(
        public ?int $id,
        public ?string $uuid,
        public int $publish_page_id,
        public string $template_key,
        public string $name,
        public string $key,
        public ?string $excerpt,
        public ?string $description,
        public array $props,
        public int $sort = 0,
        public bool $is_active = true,
    ) {}
}
