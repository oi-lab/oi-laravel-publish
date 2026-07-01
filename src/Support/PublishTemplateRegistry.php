<?php

namespace OiLab\OiLaravelPublish\Support;

use OiLab\OiLaravelPublish\Data\PublishTemplateData;
use OiLab\OiLaravelPublish\Enums\PublishTemplateType;

/**
 * PublishTemplateRegistry
 *
 * In-memory catalogue of the page and block templates available to the
 * application. It is hydrated from `config('oi-laravel-publish.templates')` on
 * construction and can be extended at runtime via {@see register()} (e.g. from
 * a host application service provider).
 */
class PublishTemplateRegistry
{
    /**
     * @var array<string, PublishTemplateData>
     */
    protected array $templates = [];

    /**
     * @param  array<int, array<string, mixed>>  $templates  Raw template definitions from config.
     */
    public function __construct(array $templates = [])
    {
        foreach ($templates as $template) {
            $this->register(PublishTemplateData::from($template));
        }
    }

    /**
     * Register (or override) a template by its key.
     */
    public function register(PublishTemplateData $template): static
    {
        $this->templates[$template->key] = $template;

        return $this;
    }

    public function has(string $key): bool
    {
        return isset($this->templates[$key]);
    }

    public function get(string $key): ?PublishTemplateData
    {
        return $this->templates[$key] ?? null;
    }

    /**
     * @return array<string, PublishTemplateData>
     */
    public function all(): array
    {
        return $this->templates;
    }

    /**
     * @return array<string, PublishTemplateData>
     */
    public function byType(PublishTemplateType $type): array
    {
        return array_filter(
            $this->templates,
            fn (PublishTemplateData $template): bool => $template->type === $type,
        );
    }

    /**
     * The keys of every registered template, optionally filtered by type.
     *
     * @return list<string>
     */
    public function keys(?PublishTemplateType $type = null): array
    {
        $templates = $type === null ? $this->templates : $this->byType($type);

        return array_keys($templates);
    }
}
