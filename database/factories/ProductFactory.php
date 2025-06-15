<?php

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = $this->faker->words(3, true);
        $price = $this->faker->numberBetween(100, 100000); // цена в рублях (int)
        $comparePrice = $this->faker->boolean(30) ? $this->faker->numberBetween($price + 100, (int)($price * 1.5)) : null;

        return [
            'name' => $name, // название товара
            'slug' => Str::slug($name), // slug товара
            'description' => $this->faker->paragraph, // описание товара
            'short_description' => $this->faker->sentence, // краткое описание товара
            'sku' => $this->faker->unique()->numerify('SKU-######'), // артикул товара
            'price' => $price, // цена товара в рублях
            'final_price' => $this->faker->numberBetween(100, 10000), // цена товара после скидки в рублях
            'compare_price' => $comparePrice, // цена до скидки в рублях
            'stock_quantity' => $this->faker->numberBetween(0, 100), // количество товара на складе
            'track_quantity' => $this->faker->boolean(80), // отслеживать количество товара на складе
            'continue_selling_when_out_of_stock' => $this->faker->boolean(20), // продолжать продавать товар когда на складе нет
            'weight' => $this->faker->randomFloat(2, 0.1, 10), // вес товара
            'weight_unit' => $this->faker->randomElement(['kg', 'g']), // единица измерения веса
            'images' => [
                $this->faker->imageUrl(640, 480, 'products'),
                $this->faker->imageUrl(640, 480, 'products'),
            ],
            'meta_title' => $this->faker->optional()->sentence, // seo
            'meta_description' => $this->faker->optional()->text(160), // seo
            'is_active' => $this->faker->boolean(85),
            'is_featured' => $this->faker->boolean(15),
            'published_at' => $this->faker->optional(0.9)->dateTimeBetween('-1 year', 'now'),
            'category_id' => Category::factory(),
        ];
    }

    /**
     * Активный товар
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => true,
        ]);
    }

    /**
     * Рекомендуемый товар
     */
    public function featured(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_featured' => true,
        ]);
    }

    /**
     * Товар в наличии
     */
    public function inStock(): static
    {
        return $this->state(fn (array $attributes) => [
            'track_quantity' => true,
            'stock_quantity' => $this->faker->numberBetween(1, 100),
        ]);
    }

    /**
     * Товар не в наличии
     */
    public function outOfStock(): static
    {
        return $this->state(fn (array $attributes) => [
            'track_quantity' => true,
            'stock_quantity' => 0,
            'continue_selling_when_out_of_stock' => false,
        ]);
    }
}
