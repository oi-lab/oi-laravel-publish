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
 * @property bool $requiresName Whether a page/block of this template must be given a `name`. False for a template whose body already carries everything it renders.
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
    ) {}

    public function isPage(): bool
    {
        return $this->type === PublishTemplateType::Page;
    }

    public function isBlock(): bool
    {
        return $this->type === PublishTemplateType::Block;
    }
}
