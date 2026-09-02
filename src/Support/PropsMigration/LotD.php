<?php

namespace OiLab\OiLaravelPublish\Support\PropsMigration;

/**
 * Lot D — the `hero`'s single style slot splits into its three structural areas.
 *
 * Exactly what {@see LotC} did for the eleven other templates, a lot later:
 * `hero` had been left out of it, holding width, margin_x, padding_y, space_y,
 * items and justify on one {@see BlockStyleData} applied to its whole column.
 * Those six move to `styles.header_area`, `styles.body_area` and
 * `styles.footer_area` — the same stored value copied to all three, which is
 * what one shared column already rendered.
 *
 * `margin_y` stays on `styles.block`: the block's own means the page rhythm
 * between it and its neighbours, and {@see BlockSectionStyleData} keeps it.
 *
 * It is a lot of its own rather than eleven templates plus one in LotC, so a
 * site that has already run C is not asked to replay it — and so the history
 * says when each template moved.
 */
final class LotD implements PropsLot
{
    /** The fields that leave `styles.block` for each of the three areas. */
    private const AREA_FIELDS = ['width', 'margin_x', 'padding_y', 'space_y', 'items', 'justify'];

    public function key(): string
    {
        return 'D';
    }

    public function description(): string
    {
        return 'Hero areas: styles.block splits into styles.{header,body,footer}_area';
    }

    public function migrate(string $templateKey, array $props): array
    {
        if ($templateKey !== 'hero') {
            return $props;
        }

        // Already split — replaying would overwrite an author's area-level
        // choice with the block's stale one.
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

        // Nothing stored under `styles.block` at all. There is nothing to carry
        // forward; PropsCast fills the three areas from BlockAreaStyleData's own
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
