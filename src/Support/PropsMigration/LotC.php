<?php

namespace OiLab\OiLaravelPublish\Support\PropsMigration;

/**
 * Lot C — the block's single style slot splits into its three structural areas.
 *
 * `styles.block` used to carry seven fields — width, margin_x, padding_y,
 * space_y, items, justify, and a margin_y that meant page rhythm — applied to
 * the block's one flex column, so its header, its body and its footer could
 * never look different from one another. Those six (not margin_y: the block's
 * own keeps its old meaning, unmigrated) move out to `styles.header_area`,
 * `styles.body_area` and `styles.footer_area`, one {@see BlockAreaStyleData}
 * each — the same stored value copied to all three, which is exactly what today
 * already renders: one shared value, worn by three regions.
 *
 * `hero`, `breadcrumb` and `reassurance` are not in this lot. The hero moved a
 * lot later, on its own — see {@see LotD}; the other two keep the single
 * `styles.block` slot, unsplit.
 */
final class LotC implements PropsLot
{
    /**
     * The templates whose `styles.block` splits into areas.
     *
     * @var list<string>
     */
    private const TEMPLATES = [
        'blockquote', 'content', 'faqs', 'form', 'grid', 'map',
        'slides', 'story', 'table', 'warranty', 'marquee',
    ];

    /**
     * The fields that leave `styles.block` for each of the three areas.
     *
     * @var list<string>
     */
    private const AREA_FIELDS = ['width', 'margin_x', 'padding_y', 'space_y', 'items', 'justify'];

    public function key(): string
    {
        return 'C';
    }

    public function description(): string
    {
        return 'Header/body/footer areas: styles.block splits into styles.{header,body,footer}_area';
    }

    public function migrate(string $templateKey, array $props): array
    {
        if (! in_array($templateKey, self::TEMPLATES, true)) {
            return $props;
        }

        // Already split — a block created after this lot shipped has no
        // `styles.block.width` left to carry forward, and replaying would
        // overwrite an author's area-level choice with the block's stale one.
        if (Props::has($props, 'styles.header_area')) {
            return $props;
        }

        $area = [];

        foreach (self::AREA_FIELDS as $field) {
            $path = "styles.block.{$field}";

            if (Props::has($props, $path)) {
                $area[$field] = Props::get($props, $path);
            }
        }

        // Nothing stored under `styles.block` at all — an unsaved block, or one
        // whose props never carried styles. There is nothing to carry forward;
        // PropsCast fills the three areas from BlockAreaStyleData's own
        // defaults at read time.
        if ($area === []) {
            return $props;
        }

        foreach (['header_area', 'body_area', 'footer_area'] as $slot) {
            $props = Props::set($props, "styles.{$slot}", $area);
        }

        foreach (self::AREA_FIELDS as $field) {
            $props = Props::forget($props, "styles.block.{$field}");
        }

        return $props;
    }
}
