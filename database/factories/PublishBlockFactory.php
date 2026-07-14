<?php

namespace OiLab\OiLaravelPublish\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use OiLab\OiLaravelPublish\Models\PublishBlock;
use OiLab\OiLaravelPublish\Models\PublishPage;
use OiLab\OiLaravelPublish\OiLaravelPublish;

/**
 * @extends Factory<PublishBlock>
 */
class PublishBlockFactory extends Factory
{
    public function modelName(): string
    {
        return OiLaravelPublish::blockModel();
    }

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = ucfirst($this->faker->unique()->words(2, true));

        return [
            'publish_page_id' => PublishPage::factory(),
            'template_key' => 'content',
            'name' => $name,
            'key' => Str::slug($name),
            'excerpt' => $this->faker->optional()->sentence(),
            // The body lives in the column, not in props.
            'description' => $this->faker->paragraph(),
            'props' => ['format' => 'markdown'],
            'sort' => 0,
            'is_active' => true,
        ];
    }

    public function forPage(PublishPage $page): static
    {
        return $this->state(fn (array $attributes): array => [
            'publish_page_id' => $page->getKey(),
        ]);
    }

    public function hero(): static
    {
        return $this->state(fn (array $attributes): array => [
            'template_key' => 'hero',
            'props' => [
                'pre' => $this->faker->words(2, true),
                'ctas' => [
                    ['label' => $this->faker->words(2, true), 'url' => '/'.$this->faker->slug()],
                ],
                'styles' => ['title' => ['align' => 'center']],
            ],
        ]);
    }

    public function warranty(): static
    {
        return $this->state(fn (array $attributes): array => [
            'template_key' => 'warranty',
            'props' => [
                'pre' => $this->faker->words(2, true),
                'items' => [
                    ['title' => $this->faker->words(2, true), 'text' => $this->faker->sentence()],
                    ['title' => $this->faker->words(2, true), 'text' => null],
                ],
            ],
        ]);
    }

    public function faqs(): static
    {
        return $this->state(fn (array $attributes): array => [
            'template_key' => 'faqs',
            'props' => [
                'items' => [
                    ['question' => $this->faker->sentence().'?', 'answer' => $this->faker->paragraph()],
                    ['question' => $this->faker->sentence().'?', 'answer' => $this->faker->paragraph()],
                ],
            ],
        ]);
    }

    public function slides(): static
    {
        return $this->state(fn (array $attributes): array => [
            'template_key' => 'slides',
            'props' => [
                'media_ratio' => 'widescreen',
                'items' => [
                    ['title' => $this->faker->words(2, true), 'caption' => $this->faker->sentence()],
                    ['title' => $this->faker->words(2, true), 'caption' => null],
                ],
            ],
        ]);
    }

    public function template(string $key): static
    {
        return $this->state(fn (array $attributes): array => [
            'template_key' => $key,
        ]);
    }
}
