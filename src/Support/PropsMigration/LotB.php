<?php

namespace OiLab\OiLaravelPublish\Support\PropsMigration;

/**
 * Lot B — the element vocabulary of phase 4.
 *
 * Six repeated elements described the same thing under six sets of names. The
 * classes now share one; these are the stored props catching up.
 *
 * The dangerous half is the renames. Spatie ignores a property it does not know
 * *without a word*, but fills a property it expects and cannot find with its
 * default — so a `caption` left unmigrated does not raise, it disappears. The
 * `--dry-run` count and the exported-seed diff are what stand in for an error
 * message here.
 */
final class LotB implements PropsLot
{
    /**
     * The element renames, per template, `from => to`.
     *
     * @var array<string, array<string, string>>
     */
    private const RENAMES = [
        'slides' => ['caption' => 'text'],
        'faqs' => ['question' => 'title', 'answer' => 'text'],
        'map' => ['label' => 'title', 'description' => 'text'],
    ];

    /**
     * The three fields that became the element's `media` slot, and the templates
     * whose elements carried them.
     *
     * @var array<string, string>
     */
    private const MEDIA = [
        'item_layout' => 'layout',
        'cover_ratio' => 'ratio',
        'max_width' => 'max_width',
    ];

    /**
     * Which props property holds a template's elements. `map` names its own,
     * which is what `capabilities.itemsProperty` says in code (decision B1); the
     * lot repeats it rather than booting the registry, so a migration stays
     * readable against the data it rewrites.
     *
     * @var array<string, string>
     */
    private const ITEMS_PROPERTY = [
        'grid' => 'items',
        'story' => 'items',
        'slides' => 'items',
        'warranty' => 'items',
        'faqs' => 'items',
        'map' => 'markers',
    ];

    public function key(): string
    {
        return 'B';
    }

    public function description(): string
    {
        return 'Phase 4 — element vocabulary: pre/title/text/icon/attachment_uuid/ctas/media';
    }

    public function migrate(string $templateKey, array $props): array
    {
        $key = self::ITEMS_PROPERTY[$templateKey] ?? null;

        if ($key === null || ! isset($props[$key]) || ! is_array($props[$key])) {
            return $props;
        }

        $props[$key] = array_map(
            fn ($item): mixed => is_array($item) ? $this->element($templateKey, $item) : $item,
            $props[$key],
        );

        return $props;
    }

    /**
     * One element, in the shared vocabulary.
     *
     * @param  array<string, mixed>  $item
     * @return array<string, mixed>
     */
    private function element(string $templateKey, array $item): array
    {
        foreach (self::RENAMES[$templateKey] ?? [] as $from => $to) {
            $item = Props::move($item, $from, $to);
        }

        $item = $this->media($item);

        return $this->slideCta($templateKey, $item);
    }

    /**
     * The three flat media fields become the `media` slot.
     *
     * @param  array<string, mixed>  $item
     * @return array<string, mixed>
     */
    private function media(array $item): array
    {
        foreach (self::MEDIA as $from => $to) {
            $item = Props::move($item, $from, "media.{$to}");
        }

        return $item;
    }

    /**
     * A slide held one nullable CTA where every other element holds a
     * collection.
     *
     * It keeps its null `position`: a slide has no slot to place an action in
     * (rule D10), so it falls in after the text, exactly where it fell before.
     *
     * @param  array<string, mixed>  $item
     * @return array<string, mixed>
     */
    private function slideCta(string $templateKey, array $item): array
    {
        if ($templateKey !== 'slides' || ! array_key_exists('cta', $item)) {
            return $item;
        }

        $cta = $item['cta'];
        unset($item['cta']);

        if (is_array($cta)) {
            $cta['position'] ??= null;
        }

        $item['ctas'] = is_array($cta) ? [$cta] : [];

        return $item;
    }
}
