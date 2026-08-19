<?php

namespace OiLab\OiLaravelPublish\Support\PropsMigration;

use OiLab\OiLaravelPublish\Enums\BlockWidth;
use OiLab\OiLaravelPublish\Enums\MapProvider;

/**
 * Lot A — the style slots of phase 3.
 *
 * Seven renames and one conversion. The conversion is A8, and it is the one that
 * matters: seven blocks used to write `max-w-7xl` into their own component while
 * `BlockStyleData::$width` defaults to `md`. Wiring the width without writing it
 * on the blocks that already had one would shrink every page on the site, with
 * no error and no log.
 */
final class LotA implements PropsLot
{
    /**
     * The width each template used to write in its component, as the enum value
     * that reproduces it. A block absent from this map keeps whatever the DTO
     * defaults to.
     *
     * `blockquote` capped at `max-w-4xl`, which the scale has no step for; it is
     * rounded to `md`. An 8 rem difference on a wide screen is the price of one
     * shared scale instead of one width per author (rule F6).
     *
     * @var array<string, BlockWidth>
     */
    private const WIDTHS = [
        'hero' => BlockWidth::Full,
        'grid' => BlockWidth::Large,
        'warranty' => BlockWidth::Large,
        'faqs' => BlockWidth::Large,
        'map' => BlockWidth::Large,
        'marquee' => BlockWidth::Large,
        'reassurance' => BlockWidth::Large,
        'breadcrumb' => BlockWidth::Large,
        'slides' => BlockWidth::Large,
        'content' => BlockWidth::Medium,
        'story' => BlockWidth::Medium,
        'blockquote' => BlockWidth::Medium,
        'form' => BlockWidth::Small,
        // The table capped nothing: it filled the section, and a wide one
        // scrolls inside its own box rather than being narrowed.
        'table' => BlockWidth::Full,
    ];

    public function key(): string
    {
        return 'A';
    }

    public function description(): string
    {
        return 'Phase 3 — style slots: cover/media, content body, carousel navigation, map provider, block width';
    }

    public function migrate(string $templateKey, array $props): array
    {
        $props = $this->media($templateKey, $props);
        $props = $this->contentBody($templateKey, $props);
        $props = $this->carouselNavigation($templateKey, $props);
        $props = $this->mapProvider($templateKey, $props);
        $props = $this->breadcrumbItems($templateKey, $props);

        return $this->blockWidth($templateKey, $props);
    }

    /**
     * A1 / A2 / A3 — the cover properties become the `media` style slot.
     *
     * A `grid` or a `story` block has no cover of its own: its images are the
     * `gallery` pool its items draw from, so its `cover_ratio` becomes the ratio
     * those items fall back to and its `cover_layout` is dropped — it arranged
     * an image that never existed.
     *
     * @param  array<string, mixed>  $props
     * @return array<string, mixed>
     */
    private function media(string $templateKey, array $props): array
    {
        if (in_array($templateKey, ['hero', 'content'], true)) {
            $props = Props::move($props, 'cover_layout', 'styles.media.layout');

            return Props::move($props, 'cover_ratio', 'styles.media.ratio');
        }

        if (in_array($templateKey, ['grid', 'story'], true)) {
            $props = Props::forget($props, 'cover_layout');

            return Props::move($props, 'cover_ratio', 'styles.media.ratio');
        }

        if ($templateKey === 'slides') {
            return Props::move($props, 'media_ratio', 'styles.media.ratio');
        }

        return $props;
    }

    /**
     * A4 — `content` styled its body under the name of the column it renders.
     *
     * @param  array<string, mixed>  $props
     * @return array<string, mixed>
     */
    private function contentBody(string $templateKey, array $props): array
    {
        return $templateKey === 'content'
            ? Props::move($props, 'styles.description', 'styles.body')
            : $props;
    }

    /**
     * A5 — the carousel navigation sat beside the slots instead of inside one.
     *
     * @param  array<string, mixed>  $props
     * @return array<string, mixed>
     */
    private function carouselNavigation(string $templateKey, array $props): array
    {
        if ($templateKey !== 'slides') {
            return $props;
        }

        $props = Props::move($props, 'styles.nav_position', 'styles.carousel.nav_position');

        return Props::move($props, 'styles.nav_size', 'styles.carousel.nav_size');
    }

    /**
     * A6 — `provider` was a free string; only `google` ever meant anything.
     *
     * The key is normalised where it exists and never added: a block that never
     * carried one takes the DTO's default, which is the same value.
     *
     * @param  array<string, mixed>  $props
     * @return array<string, mixed>
     */
    private function mapProvider(string $templateKey, array $props): array
    {
        if ($templateKey !== 'map' || ! array_key_exists('provider', $props)) {
            return $props;
        }

        $props['provider'] = $props['provider'] === MapProvider::Google->value
            ? MapProvider::Google->value
            : MapProvider::OpenStreetMap->value;

        return $props;
    }

    /**
     * A7 — the trail comes from the page tree; the prop was never read.
     *
     * @param  array<string, mixed>  $props
     * @return array<string, mixed>
     */
    private function breadcrumbItems(string $templateKey, array $props): array
    {
        return $templateKey === 'breadcrumb'
            ? Props::forget($props, 'items')
            : $props;
    }

    /**
     * A8 — the width each block wrote in its own component, written as data.
     *
     * It **overwrites** whatever is stored, unlike every other transformation of
     * the lot. `BlockStyleData::$width` has existed from the first day and no
     * screen ever offered it, so a stored value is the DTO's default (`md`) and
     * never an author's choice — while seven templates rendered at `max-w-7xl`.
     * Leaving those alone is exactly how the whole site would silently shrink
     * the day the width is finally read.
     *
     * Replaying the lot writes the same value again, so it stays idempotent.
     *
     * @param  array<string, mixed>  $props
     * @return array<string, mixed>
     */
    private function blockWidth(string $templateKey, array $props): array
    {
        $width = self::WIDTHS[$templateKey] ?? null;

        return $width === null
            ? $props
            : Props::set($props, 'styles.block.width', $width->value);
    }
}
