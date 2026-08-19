<?php

namespace OiLab\OiLaravelPublish\Data;

use OiLab\OiLaravelPublish\Enums\PublishTemplateType;
use Spatie\LaravelData\Data;

/**
 * PublishTemplateData
 *
 * The "static model" describing how a page or block is processed and rendered.
 * Templates are code/config defined (see PublishTemplateRegistry) rather than
 * stored in the database; pages and blocks reference one by its string `key`.
 *
 * @property array<string, mixed> $props Default props seeded onto new pages/blocks using this template.
 * @property class-string<PropsData>|null $propsClass Typed props Data class used by the PropsCast.
 * @property list<string> $allowedBlocks For page templates: the block template keys allowed inside, in suggested order.
 * @property bool $requiresName Whether a page/block of this template must be given a `name`. False for a template whose body already carries everything it renders. Constrained by the capabilities: see {@see requiresName()}.
 * @property BlockCapabilitiesData $capabilities For block templates: what the block knows how to do. Page templates leave it empty.
 */
class PublishTemplateData extends Data
{
    /**
     * @param  array<string, mixed>  $props
     * @param  array<int, string>  $allowedBlocks
     */
    public function __construct(
        public string $key,
        public string $name,
        public PublishTemplateType $type,
        public ?string $description = null,
        public array $props = [],
        public ?string $propsClass = null,
        public array $allowedBlocks = [],
        public bool $requiresName = true,
        public BlockCapabilitiesData $capabilities = new BlockCapabilitiesData,
    ) {}

    /**
     * Whether a block of this template must be given a `name`.
     *
     * The capabilities constrain the declaration rather than replacing it: a
     * block that does not render its `name` cannot require one, while a block
     * that does render it stays free to make it optional — a `content` block
     * carries everything it shows in its body, and its title is a nicety.
     *
     * The column itself stays NOT NULL: it is the asking that disappears, not
     * the value. The editor fills it with the template's own name.
     */
    public function requiresName(): bool
    {
        return $this->capabilities->title && $this->requiresName;
    }

    public function isPage(): bool
    {
        return $this->type === PublishTemplateType::Page;
    }

    public function isBlock(): bool
    {
        return $this->type === PublishTemplateType::Block;
    }
}
