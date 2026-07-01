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
            'description' => $this->faker->optional()->paragraph(),
            'props' => ['body' => $this->faker->paragraph(), 'format' => 'markdown'],
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
            'props' => ['heading' => $this->faker->sentence(), 'alignment' => 'center'],
        ]);
    }

    public function template(string $key): static
    {
        return $this->state(fn (array $attributes): array => [
            'template_key' => $key,
        ]);
    }
}
