<?php

namespace OiLab\OiLaravelPublish\Data;

use OiLab\OiLaravelAttachments\Data\AttachmentData;
use OiLab\OiLaravelAttachments\Models\Attachment;
use OiLab\OiLaravelPublish\Data\Blocks\BlockquoteData;
use OiLab\OiLaravelPublish\Data\Blocks\BreadcrumbData;
use OiLab\OiLaravelPublish\Data\Blocks\ContentData;
use OiLab\OiLaravelPublish\Data\Blocks\FeaturesData;
use OiLab\OiLaravelPublish\Data\Blocks\FormData;
use OiLab\OiLaravelPublish\Data\Blocks\HeroData;
use OiLab\OiLaravelPublish\Data\Blocks\MapData;
use OiLab\OiLaravelPublish\Data\Blocks\SlidesData;
use OiLab\OiLaravelPublish\Data\Blocks\TableData;
use OiLab\OiLaravelPublish\Data\Blocks\WarrantyData;
use OiLab\OiLaravelPublish\Models\PublishBlock;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Optional;

/**
 * PublishBlockData
 *
 * Serialisable representation of a {@see PublishBlock}. `props` is the raw,
 * flattened props map (from the model's typed PropsData via `toProps()`), so the
 * DTO JSON is uniform. Its shape conforms to the block template's typed props —
 * the `@param` union below makes oi-laravel-ts emit `props` as the union of the
 * typed block interfaces (`IHeroData | IFeaturesData | ...`).
 *
 * `props` never carries content: the title, lead and body of a block are its
 * `name`, `excerpt` and `description` here. Props hold what is specific to the
 * template, plus the two cross-cutting keys `ctas` and `styles`.
 *
 * That union carries no discriminant of its own, and its `array<string, mixed>`
 * member — the shape of a block whose template declares no typed props class —
 * widens to `Record<string, unknown>`, which absorbs every other member. The
 * front end must therefore narrow on `template_key`, the only discriminant, and
 * cast: `props as IHeroData` once `template_key === 'hero'`.
 *
 * `cover` and `slides` are `Optional`: they appear in the JSON only when their
 * relation was eager-loaded. An absent key means "not loaded", where a null
 * `cover` means "loaded, and there is no cover".
 */
class PublishBlockData extends Data
{
    /**
     * @param  HeroData|FeaturesData|BlockquoteData|ContentData|FormData|SlidesData|BreadcrumbData|MapData|TableData|WarrantyData|array<string, mixed>  $props
     * @param  AttachmentData[]|Optional  $slides
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
        public AttachmentData|Optional|null $cover = new Optional,
        public array|Optional $slides = new Optional,
    ) {}

    /**
     * Build the DTO from its model.
     *
     * The model's `props` is a typed {@see PropsData}, which the DTO carries as
     * the raw map produced by `toProps()`. Without this factory,
     * `PublishBlockData::from($block)` hands that PropsData straight to the
     * `array $props` parameter and dies with a TypeError.
     *
     * Declaring the model here also lets oi-laravel-ts pair the DTO with its
     * model, which is what `data_replaces_model` introspects.
     */
    public static function fromModel(PublishBlock $block): self
    {
        return new self(
            id: $block->id,
            uuid: $block->uuid,
            publish_page_id: $block->publish_page_id,
            template_key: $block->template_key,
            name: $block->name,
            key: $block->key,
            excerpt: $block->excerpt,
            description: $block->description,
            props: $block->props->toProps(),
            sort: $block->sort,
            is_active: $block->is_active,
            cover: $block->relationLoaded('cover') ? $block->cover?->toData() : new Optional,
            slides: $block->relationLoaded('slides')
                ? $block->slides->map(fn (Attachment $slide): AttachmentData => $slide->toData())->all()
                : new Optional,
        );
    }
}
