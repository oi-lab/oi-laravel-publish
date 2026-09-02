<?php

namespace OiLab\OiLaravelPublish\Data;

use Spatie\LaravelData\Data;

/**
 * BlockCapabilitiesData
 *
 * What a block template knows how to do, declared once and read everywhere: the
 * console builds its editor from it, the attachments service derives the
 * collections a block accepts, the store request derives whether a name is
 * required, and the public component is held to it by an architecture test.
 *
 * It replaces the maps that each answered part of the same question on their
 * own — `PublishBlockAttachmentsService::COLLECTIONS`, the console's
 * `HIDDEN_META_FIELDS`, the `switch (template_key)` of the content service.
 *
 * Capabilities are static: they depend neither on the page, nor on the user,
 * nor on the stored data. A block declaring none is legitimate — a breadcrumb
 * authors nothing — and keeps its block style slot like every other.
 */
class BlockCapabilitiesData extends Data
{
    /**
     * @param  bool  $pre  The block renders `props.pre`.
     * @param  bool  $title  The block renders the `name` column.
     * @param  bool  $excerpt  The block renders the `excerpt` column.
     * @param  bool  $body  The block renders the `description` column.
     * @param  array<int, string>  $media  The attachment collections it renders: `cover`, `slides`, `gallery`. Written `array<int, string>` rather than `list<string>`: oi:gen-ts emits `unknown` for the latter, silently.
     * @param  class-string|null  $itemsClass  The Data of a repeated element, or null.
     * @param  string  $itemsProperty  The props property holding those elements. `map` names its own `markers`, at the cost of one field here rather than a rename that would contradict the block's own vocabulary.
     * @param  bool  $ctas  The block carries a collection of {@see CtaData}.
     */
    public function __construct(
        public bool $pre = false,
        public bool $title = false,
        public bool $excerpt = false,
        public bool $body = false,
        public array $media = [],
        public ?string $itemsClass = null,
        public string $itemsProperty = 'items',
        public bool $ctas = false,
    ) {}

    /**
     * Whether the block renders this attachment collection — the single check
     * the synchronisation endpoint validates against.
     */
    public function hasMedia(string $collection): bool
    {
        return in_array($collection, $this->media, true);
    }

    /**
     * Whether the block repeats elements.
     */
    public function hasItems(): bool
    {
        return $this->itemsClass !== null;
    }

    /**
     * The collections that hold exactly one file: a block's cover, and the video
     * it plays in place of it.
     *
     * @var array<int, string>
     */
    private const SINGLE_FILE_COLLECTIONS = ['cover', 'video'];

    /**
     * Whether a collection holds a single file.
     *
     * Cardinality belongs to the collection, not to the template: a `cover` is
     * one image wherever it hangs, and every other collection is an ordered list
     * capped by configuration. A block gaining a `gallery` therefore inherits
     * the right ceiling without anyone thinking about it.
     */
    public function isSingleFile(string $collection): bool
    {
        return in_array($collection, self::SINGLE_FILE_COLLECTIONS, true);
    }
}
