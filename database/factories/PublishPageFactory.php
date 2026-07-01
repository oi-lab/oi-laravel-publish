<?php

namespace OiLab\OiLaravelPublish\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use OiLab\OiLaravelPublish\Models\PublishPage;
use OiLab\OiLaravelPublish\OiLaravelPublish;

/**
 * @extends Factory<PublishPage>
 */
class PublishPageFactory extends Factory
{
    public function modelName(): string
    {
        return OiLaravelPublish::pageModel();
    }

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = ucfirst($this->faker->unique()->words(3, true));

        return [
            'template_key' => 'default',
            'name' => $name,
            'slug' => Str::slug($name),
            'excerpt' => $this->faker->optional()->sentence(),
            'description' => $this->faker->optional()->paragraph(),
            'props' => [],
            'sort' => 0,
            'is_active' => true,
        ];
    }

    public function landing(): static
    {
        return $this->state(fn (array $attributes): array => [
            'template_key' => 'landing',
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes): array => [
            'is_active' => false,
        ]);
    }

    public function childOf(PublishPage $parent): static
    {
        return $this->state(fn (array $attributes): array => [
            'parent_id' => $parent->getKey(),
        ]);
    }
}
