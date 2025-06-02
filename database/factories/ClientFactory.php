<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Client>
 */
class ClientFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'first_name' => $this->faker->firstName,
            'last_name' => $this->faker->lastName,
            'email' => $this->faker->unique()->safeEmail,
            'phone' => $this->faker->phoneNumber,
            'date_of_birth' => $this->faker->dateTimeBetween('-20 years', '-10 years'),
            'gender' => $this->faker->optional()->randomElement(['male', 'female']),
            'addresses' => [
                [
                    'type' => 'shipping', // доставка
                    'is_default' => true, // основной адрес
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
            ],
            'accepts_marketing' => $this->faker->boolean(60), // согласие на получение маркетинговых материалов
            'email_verified_at' => $this->faker->optional(0.8)->dateTimeBetween('-1 year', 'now'), // подтверждение email
            'is_active' => $this->faker->boolean(95), // активный клиент
        ];
    }

    /**
     * Активный клиент
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => true,
        ]);
    }

    /**
     * Клиент с подтвержденным email
     */
    public function verified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => now(),
        ]);
    }

    /**
     * Клиент согласившийся на маркетинг
     */
    public function marketingAccepted(): static
    {
        return $this->state(fn (array $attributes) => [
            'accepts_marketing' => true,
        ]);
    }

    /**
     * Клиент с несколькими адресами
     */
    public function withMultipleAddresses(): static
    {
        return $this->state(fn (array $attributes) => [
            'addresses' => [
                [
                    'type' => 'shipping',
                    'is_default' => true,
                    'first_name' => $this->faker->firstName,
                    'last_name' => $this->faker->lastName,
                    'street' => $this->faker->streetAddress,
                    'city' => $this->faker->city,
                    'state' => $this->faker->state,
                    'postal_code' => $this->faker->postcode,
                    'country' => 'Russia',
                    'phone' => $this->faker->phoneNumber,
                ],
                [
                    'type' => 'billing',
                    'is_default' => false,
                    'first_name' => $this->faker->firstName,
                    'last_name' => $this->faker->lastName,
                    'company' => $this->faker->company,
                    'street' => $this->faker->streetAddress,
                    'city' => $this->faker->city,
                    'state' => $this->faker->state,
                    'postal_code' => $this->faker->postcode,
                    'country' => 'Russia',
                    'phone' => $this->faker->phoneNumber,
                ],
            ],
        ]);
    }
}
