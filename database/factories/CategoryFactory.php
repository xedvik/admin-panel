<?php

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Category>
 */
class CategoryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = $this->faker->words(2, true);

        return [
            'name' => $name, // название категории
            'slug' => Str::slug($name), // slug категории
            'description' => $this->faker->optional()->paragraph, // описание категории
            'image' => $this->faker->optional()->imageUrl(), // изображение категории
            'meta_title' => $this->faker->optional()->sentence, // seo
            'meta_description' => $this->faker->optional()->text(160), // seo
            'sort_order' => $this->faker->numberBetween(0, 100), // порядок сортировки
            'is_active' => $this->faker->boolean(90), // активная категория
        ];
    }

    /**
     * Активная категория
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => true,
        ]);
    }

    /**
     * Неактивная категория
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    /**
     * Категория с родителем
     */
    public function withParent(?int $parentId = null): static
    {
        return $this->state(fn (array $attributes) => [
            'parent_id' => $parentId ?? Category::factory(),
        ]);
    }
}
