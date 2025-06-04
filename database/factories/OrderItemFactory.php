<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\OrderItem>
 */
class OrderItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $quantity = $this->faker->numberBetween(1, 5);
        $productPrice = $this->faker->numberBetween(100, 10000); // цена в рублях (int)
        $totalPrice = $quantity * $productPrice;

        // Создаем различные варианты JSON объектов для product_variant
        $variantOptions = [
            // Вариант 1: размер и цвет
            [
                'Размер' => $this->faker->randomElement(['S', 'M', 'L', 'XL']),
                'Цвет' => $this->faker->colorName,
            ],
            // Вариант 2: материал и стиль
            [
                'Материал' => $this->faker->word,
                'Стиль' => $this->faker->word,
            ],
            // Вариант 3: размер, цвет и материал
            [
                'Размер' => $this->faker->randomElement(['42', '44', '46', '48']),
                'Цвет' => $this->faker->colorName,
                'Материал' => $this->faker->word,
            ],
            // Вариант 4: null (без варианта)
            null,
        ];

        return [
            'order_id' => Order::factory(), // заказ
            'product_id' => Product::factory(), // товар
            'product_name' => $this->faker->words(3, true), // название товара
            'product_sku' => $this->faker->numerify('SKU-######'), // артикул товара
            'quantity' => $quantity, // количество товара
            'product_price' => $productPrice, // цена товара в рублях
            'total_price' => $totalPrice, // сумма товара в рублях
            'product_variant' => $this->faker->randomElement($variantOptions), // вариант товара как JSON объект
        ];
    }

    /**
     * Позиция с существующим товаром
     */
    public function withProduct(Product $product): static
    {
        return $this->state(fn (array $attributes) => [
            'product_id' => $product->id,
            'product_name' => $product->name,
            'product_sku' => $product->sku,
            'product_price' => $product->price,
        ]);
    }

    /**
     * Позиция с определенным количеством
     */
    public function withQuantity(int $quantity): static
    {
        return $this->state(function (array $attributes) use ($quantity) {
            $totalPrice = $quantity * $attributes['product_price'];

            return [
                'quantity' => $quantity,
                'total_price' => $totalPrice,
            ];
        });
    }
}
