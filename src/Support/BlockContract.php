<?php

namespace OiLab\OiLaravelPublish\Support;

use OiLab\OiLaravelPublish\Data\PropsData;
use OiLab\OiLaravelPublish\Data\PublishTemplateData;
use ReflectionClass;
use ReflectionNamedType;
use ReflectionParameter;

/**
 * The contract between what a block declares and what it actually carries.
 *
 * Rule R-B: what is declared is rendered, what is rendered is declared. A
 * `BlockCapabilitiesData` is the single declaration of what a block knows how to
 * do; this class is what stops it from drifting away from the classes behind it.
 *
 * It lives in `src/` rather than in a test because it sweeps *the registry*: a
 * host application that registers its own block templates — this project's
 * `marquee` and `reassurance` — is held to the same contract without listing
 * anything anywhere.
 *
 * Each check answers a violation string, or nothing. The tests turn a non-empty
 * list into a failure; the strings themselves are what a developer reads.
 */
final class BlockContract
{
    /**
     * Exceptions assumed, each with its reason. A new entry is a decision, not a
     * tolerance: nothing is skipped silently.
     *
     * - `faqs.layout-slot`: an accordion is a stack, neither a grid nor a
     *   carousel, so it declares items without `styles.list` or
     *   `styles.carousel`.
     * - `map.layout-slot`: a pin is placed by its coordinates. Markers are
     *   repeated elements with no layout to give them.
     * - `blockquote.body`: the quotation *is* the body, and it is served by the
     *   block's own `quote` slot rather than by a `body` one.
     * - `blockquote.quote`: the other half of the same decision — the slot
     *   answers the `body` capability under another name.
     * - `table.table`: the `table` slot styles the grid itself (density, rules,
     *   zebra). The block's structured content is `headers` and `rows`, which
     *   are not a repeated element class.
     * - `reassurance.list`: the gages are the site's and fixed in the component
     *   until phase 6 gives them a setting (decision B4). The slot styles the
     *   row they are laid out in.
     *
     * @var array<string, string>
     */
    public const EXCEPTIONS = [
        'faqs.layout-slot' => 'accordéon : une pile, ni grille ni carrousel',
        'map.layout-slot' => 'un marqueur est placé par ses coordonnées, pas par une grille',
        'blockquote.body' => 'la citation est le corps, servie par le slot quote',
        'blockquote.quote' => 'le slot quote répond à la capacité body',
        'table.table' => 'le slot table met en forme la grille, dont le contenu est headers/rows',
        'reassurance.list' => 'gages figés dans le composant jusqu’à la phase 6 (décision B4)',
    ];

    /**
     * Which capability each style slot answers.
     *
     * `block` answers none: every block is presented, even one that authors
     * nothing. `header_area`, `body_area` and `footer_area` answer none either,
     * for the same reason: a block that carries three areas instead of one
     * `block` slot still needs all three laid out, whatever it renders inside
     * them. A slot absent from this map and from the exceptions is a slot
     * nobody can explain, which is the point of the check.
     *
     * @var array<string, string>
     */
    private const SLOT_CAPABILITY = [
        'block' => 'always',
        'header_area' => 'always',
        'body_area' => 'always',
        'footer_area' => 'always',
        'pre' => 'pre',
        'title' => 'title',
        'excerpt' => 'excerpt',
        'body' => 'body',
        'media' => 'media',
        'ctas' => 'ctas',
        'list' => 'items',
        'carousel' => 'items',
    ];

    /**
     * The closed vocabulary of a repeated element, in its fixed order.
     *
     * Six elements across the catalogue describe the same thing — an overline, a
     * title, a body, an icon, an image, actions, a media arrangement — and used
     * to say it six ways. One vocabulary means one editor can edit them all, and
     * one markdown rule can render them all.
     *
     * A field outside this list is legitimate when it is the element's own
     * domain — a marker's coordinates — and it comes **after** the shared words.
     *
     * @var list<string>
     */
    public const ITEM_VOCABULARY = [
        'pre',
        'title',
        'text',
        'icon',
        'attachment_uuid',
        'ctas',
        'media',
    ];

    /**
     * The names the vocabulary replaced, each of which said the same thing under
     * a word nothing shared could act on.
     *
     * @var array<string, string>
     */
    public const BANNED_ITEM_PROPERTIES = [
        'caption' => 'text',
        'question' => 'title',
        'answer' => 'text',
        'label' => 'title',
        'description' => 'text',
        'cta' => 'ctas',
        'item_layout' => 'media.layout',
        'cover_ratio' => 'media.ratio',
        'max_width' => 'media.max_width',
    ];

    /**
     * Everything this template gets wrong, in the order the contract states it.
     *
     * @return list<string>
     */
    public static function violations(PublishTemplateData $template): array
    {
        return [
            ...self::propsViolations($template),
            ...self::declaredCapabilityViolations($template),
            ...self::unexplainedSlotViolations($template),
            ...self::itemVocabularyViolations($template),
        ];
    }

    /**
     * The element class of a template, held to the closed vocabulary.
     *
     * @return list<string>
     */
    private static function itemVocabularyViolations(PublishTemplateData $template): array
    {
        $itemsClass = $template->capabilities->itemsClass;

        if ($itemsClass === null) {
            return [];
        }

        $properties = self::propsProperties($itemsClass);
        $violations = [];

        foreach ($properties as $property) {
            if (array_key_exists($property, self::BANNED_ITEM_PROPERTIES)) {
                $replacement = self::BANNED_ITEM_PROPERTIES[$property];
                $violations[] = "`{$template->key}` names an element property `{$property}`; the vocabulary calls it `{$replacement}`.";
            }
        }

        $shared = array_values(array_intersect($properties, self::ITEM_VOCABULARY));
        $expected = array_values(array_intersect(self::ITEM_VOCABULARY, $properties));

        if ($shared !== $expected) {
            $violations[] = sprintf(
                '`%s` declares its element properties as [%s]; the vocabulary orders them [%s].',
                $template->key,
                implode(', ', $shared),
                implode(', ', $expected),
            );
        }

        // Domain fields — a marker's coordinates — trail the shared words rather
        // than being threaded through them.
        $lastShared = -1;
        foreach ($properties as $index => $property) {
            if (in_array($property, self::ITEM_VOCABULARY, true)) {
                $lastShared = $index;
            }
        }

        foreach ($properties as $index => $property) {
            if ($index < $lastShared && ! in_array($property, self::ITEM_VOCABULARY, true)) {
                $violations[] = "`{$template->key}` puts its own `{$property}` in the middle of the shared vocabulary; domain fields come after it.";
            }
        }

        return $violations;
    }

    /**
     * The names of a props class's constructor properties.
     *
     * @param  class-string  $propsClass
     * @return list<string>
     */
    public static function propsProperties(string $propsClass): array
    {
        $constructor = (new ReflectionClass($propsClass))->getConstructor();

        return array_map(
            static fn (ReflectionParameter $parameter): string => $parameter->getName(),
            $constructor?->getParameters() ?? [],
        );
    }

    /**
     * The style slots a template declares: the constructor properties of the
     * class typing its props' `styles` property.
     *
     * @return list<string>
     */
    public static function styleSlots(?string $propsClass): array
    {
        if ($propsClass === null) {
            return [];
        }

        $constructor = (new ReflectionClass($propsClass))->getConstructor();

        foreach ($constructor?->getParameters() ?? [] as $parameter) {
            if ($parameter->getName() !== 'styles') {
                continue;
            }

            $type = $parameter->getType();

            return $type instanceof ReflectionNamedType && class_exists($type->getName())
                ? self::propsProperties($type->getName())
                : [];
        }

        return [];
    }

    /** Whether the exception list excuses this template from that check. */
    private static function excused(PublishTemplateData $template, string $check): bool
    {
        return array_key_exists("{$template->key}.{$check}", self::EXCEPTIONS);
    }

    /**
     * A block with no props class carries nothing, so it can declare nothing;
     * a declared `pre`, items property or `ctas` collection has to be there.
     *
     * @return list<string>
     */
    private static function propsViolations(PublishTemplateData $template): array
    {
        $capabilities = $template->capabilities;
        $properties = $template->propsClass === null ? [] : self::propsProperties($template->propsClass);
        $violations = [];

        if (in_array('pre', $properties, true) !== $capabilities->pre) {
            $violations[] = "`{$template->key}` declares pre=".var_export($capabilities->pre, true).' but its props class says otherwise.';
        }

        if ($capabilities->hasItems() && ! in_array($capabilities->itemsProperty, $properties, true)) {
            $violations[] = "`{$template->key}` declares its items under `{$capabilities->itemsProperty}`, which its props class does not carry.";
        }

        if ($capabilities->ctas && ! in_array('ctas', $properties, true)) {
            $violations[] = "`{$template->key}` declares ctas its props class does not carry.";
        }

        if ($template->propsClass !== null && ! is_subclass_of($template->propsClass, PropsData::class)) {
            $violations[] = "`{$template->key}` names a props class that is not a PropsData.";
        }

        return $violations;
    }

    /**
     * Every declared capability answered by the style slot that presents it.
     *
     * @return list<string>
     */
    private static function declaredCapabilityViolations(PublishTemplateData $template): array
    {
        $capabilities = $template->capabilities;
        $slots = self::styleSlots($template->propsClass);
        $violations = [];

        $required = [
            'pre' => $capabilities->pre,
            'title' => $capabilities->title,
            'excerpt' => $capabilities->excerpt,
            'body' => $capabilities->body,
            'media' => $capabilities->media !== [],
            'ctas' => $capabilities->ctas,
        ];

        foreach ($required as $slot => $declared) {
            if ($declared && ! in_array($slot, $slots, true) && ! self::excused($template, $slot)) {
                $violations[] = "`{$template->key}` renders {$slot} but has no `styles.{$slot}` slot to present it.";
            }
        }

        $laysOut = in_array('list', $slots, true) || in_array('carousel', $slots, true);

        if ($capabilities->hasItems() && ! $laysOut && ! self::excused($template, 'layout-slot')) {
            $violations[] = "`{$template->key}` repeats elements but has neither a `styles.list` nor a `styles.carousel` slot to lay them out.";
        }

        return $violations;
    }

    /**
     * And the other way round: no slot without a capability behind it.
     *
     * @return list<string>
     */
    private static function unexplainedSlotViolations(PublishTemplateData $template): array
    {
        $capabilities = $template->capabilities;
        $violations = [];

        foreach (self::styleSlots($template->propsClass) as $slot) {
            if (self::excused($template, $slot)) {
                continue;
            }

            $answered = match (self::SLOT_CAPABILITY[$slot] ?? null) {
                'always' => true,
                'pre' => $capabilities->pre,
                'title' => $capabilities->title,
                'excerpt' => $capabilities->excerpt,
                'body' => $capabilities->body,
                'media' => $capabilities->media !== [],
                'ctas' => $capabilities->ctas,
                'items' => $capabilities->hasItems(),
                default => false,
            };

            if (! $answered) {
                $violations[] = "`{$template->key}` carries a `styles.{$slot}` slot that answers no declared capability.";
            }
        }

        return $violations;
    }
}
