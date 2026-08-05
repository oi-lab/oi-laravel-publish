<?php

namespace OiLab\OiLaravelPublish\Data;

use OiLab\OiLaravelAttachments\Data\AttachmentData;
use OiLab\OiLaravelPublish\Data\Pages\PagePropsData;
use OiLab\OiLaravelPublish\Models\PublishPage;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Optional;

/**
 * PublishPageData
 *
 * Serialisable representation of a {@see PublishPage}. `props` is the raw,
 * flattened props map (from the model's typed PropsData via `toProps()`), so the
 * DTO JSON is uniform regardless of whether the template declares a typed class.
 *
 * Its shape conforms to the page template's typed props — the `@param` union
 * below makes oi-laravel-ts emit `props` as `IPagePropsData | Record<string,
 * unknown>`, the second member covering a host page template that declares no
 * `propsClass`. That member absorbs the first, so the front end reads
 * `props as IPagePropsData` once it knows the template is a typed one.
 *
 * `cover` is an `Optional`: it appears in the JSON only when the relation was
 * eager-loaded. An absent key therefore means "not loaded", where a null value
 * means "loaded, and there is no cover".
 */
class PublishPageData extends Data
{
    /**
     * @param  PagePropsData|array<string, mixed>  $props
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
        public AttachmentData|Optional|null $cover = new Optional,
    ) {}

    /**
     * Build the DTO from its model.
     *
     * The model's `props` is a typed {@see PropsData}, which the DTO carries as
     * the raw map produced by `toProps()`. Without this factory,
     * `PublishPageData::from($page)` hands that PropsData straight to the
     * `array $props` parameter and dies with a TypeError.
     *
     * Declaring the model here also lets oi-laravel-ts pair the DTO with its
     * model, which is what `data_replaces_model` introspects.
     */
    public static function fromModel(PublishPage $page): self
    {
        return new self(
            id: $page->id,
            uuid: $page->uuid,
            parent_id: $page->parent_id,
            template_key: $page->template_key,
            name: $page->name,
            slug: $page->slug,
            excerpt: $page->excerpt,
            description: $page->description,
            props: $page->props->toProps(),
            sort: $page->sort,
            is_active: $page->is_active,
            cover: $page->relationLoaded('cover') ? $page->cover?->toData() : new Optional,
        );
    }
}
