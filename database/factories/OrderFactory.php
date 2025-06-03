<?php

namespace Database\Factories;

use App\Models\Client;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Order>
 */
class OrderFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $subtotal = $this->faker->numberBetween(200, 50000); // сумма заказа без скидок и доставки в рублях
        $taxAmount = (int)($subtotal * 0.2); // 20% налог
        $shippingAmount = $this->faker->numberBetween(0, 5000); // сумма доставки в рублях
        $discountAmount = $this->faker->boolean(30) ? $this->faker->numberBetween(0, (int)($subtotal * 0.2)) : 0; // сумма скидки
        $totalAmount = $subtotal + $taxAmount + $shippingAmount - $discountAmount; // сумма заказа

        return [
            'order_number' => 'ORD-' . $this->faker->unique()->numerify('######'), // номер заказа
            'client_id' => Client::factory(), // клиент
            'status' => $this->faker->randomElement(['pending', 'processing', 'shipped', 'delivered', 'cancelled']), // статус заказа
            'subtotal' => $subtotal, // сумма заказа без скидок и доставки
            'tax_amount' => $taxAmount, // сумма налога
            'shipping_amount' => $shippingAmount, // сумма доставки
            'discount_amount' => $discountAmount, // сумма скидки
            'total_amount' => $totalAmount, // сумма заказа
            'currency' => 'RUB', // валюта заказа
            'payment_status' => $this->faker->randomElement(['pending', 'paid', 'failed', 'refunded']), // статус оплаты
            'payment_method' => $this->faker->randomElement(['card', 'cash', 'bank_transfer', 'qr_code']), // метод оплаты
            'billing_address' => [
                'first_name' => $this->faker->firstName, // имя
                'last_name' => $this->faker->lastName, // фамилия
                'company' => $this->faker->optional()->company, // компания
                'street' => $this->faker->streetAddress, // улица
                'city' => $this->faker->city, // город
                'state' => $this->faker->state, // область
                'postal_code' => $this->faker->postcode, // почтовый индекс
                'country' => 'Russia', // страна
                'phone' => $this->faker->phoneNumber, // телефон
            ],
            'shipping_address' => [
                'first_name' => $this->faker->firstName, // имя
                'last_name' => $this->faker->lastName, // фамилия
                'company' => $this->faker->optional()->company, // компания
                'street' => $this->faker->streetAddress, // улица
                'city' => $this->faker->city, // город
                'state' => $this->faker->state, // область
                'postal_code' => $this->faker->postcode, // почтовый индекс
                'country' => 'Russia', // страна
                'phone' => $this->faker->phoneNumber, // телефон
            ],
            'notes' => $this->faker->optional()->paragraph, // примечания к заказу
            'shipped_at' => $this->faker->optional(0.6)->dateTimeBetween('-1 month', 'now'), // дата отправки заказа
            'delivered_at' => $this->faker->optional(0.4)->dateTimeBetween('-1 month', 'now'), // дата доставки заказа
        ];
    }

    /**
     * Заказ в ожидании
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'pending',
            'payment_status' => 'pending',
            'shipped_at' => null,
            'delivered_at' => null,
        ]);
    }

    /**
     * Заказ в обработке
     */
    public function processing(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'processing',
            'payment_status' => 'paid',
            'shipped_at' => null,
            'delivered_at' => null,
        ]);
    }

    /**
     * Отправленный заказ
     */
    public function shipped(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'shipped',
            'payment_status' => 'paid',
            'shipped_at' => now(),
            'delivered_at' => null,
        ]);
    }

    /**
     * Доставленный заказ
     */
    public function delivered(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'delivered',
            'payment_status' => 'paid',
            'shipped_at' => $this->faker->dateTimeBetween('-1 week', '-3 days'),
            'delivered_at' => now(),
        ]);
    }

    /**
     * Оплаченный заказ
     */
    public function paid(): static
    {
        return $this->state(fn (array $attributes) => [
            'payment_status' => 'paid',
        ]);
    }
}
