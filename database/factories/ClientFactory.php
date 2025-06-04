<?php

namespace Database\Factories;

use App\Models\ClientAddress;
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
            'phone' => $this->generateRussianPhone(),
            'date_of_birth' => $this->faker->dateTimeBetween('-20 years', '-10 years'),
            'gender' => $this->faker->optional()->randomElement(['male', 'female']),
            'accepts_marketing' => $this->faker->boolean(60),
            'email_verified_at' => $this->faker->optional(0.8)->dateTimeBetween('-1 year', 'now'),
            'is_active' => $this->faker->boolean(95),
        ];
    }

    /**
     * Генерация российского номера телефона
     */
    private function generateRussianPhone(): string
    {
        // Генерируем номер в формате 8XXXXXXXXXX
        // Российские мобильные коды: 900-999
        $mobileCode = $this->faker->numberBetween(900, 999);
        $number = $this->faker->numerify('#######');

        return "8{$mobileCode}{$number}";
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
     * Создать базовый адрес для клиента
     */
    public function configure()
    {
        return $this->afterCreating(function ($client) {
            // Создаем базовый адрес доставки для каждого клиента
            ClientAddress::create([
                'client_id' => $client->id,
                'type' => $this->faker->randomElement(['shipping', 'billing']),
                'label' => $this->faker->randomElement(['Дом', 'Офис', 'Основной']),
                'is_default' => true,
                'first_name' => $this->faker->firstName,
                'last_name' => $this->faker->lastName,
                'company' => $this->faker->optional()->company,
                'street' => $this->faker->streetAddress,
                'city' => $this->faker->city,
                'state' => $this->faker->state,
                'postal_code' => $this->faker->postcode,
                'country' => 'Russia',
                'phone' => $this->generateRussianPhone(),
            ]);
        });
    }

    /**
     * Клиент с несколькими адресами
     */
    public function withMultipleAddresses(): static
    {
        return $this->afterCreating(function ($client) {
            // Основной адрес доставки
            ClientAddress::create([
                'client_id' => $client->id,
                    'type' => 'shipping',
                'label' => 'Дом',
                    'is_default' => true,
                    'first_name' => $this->faker->firstName,
                    'last_name' => $this->faker->lastName,
                'company' => $this->faker->optional(30)->company,
                'street' => $this->faker->streetAddress,
                'city' => $this->faker->city,
                'state' => $this->faker->state,
                'postal_code' => $this->faker->postcode,
                'country' => 'Russia',
                'phone' => $this->generateRussianPhone(),
            ]);

            // Рабочий адрес доставки
            ClientAddress::create([
                'client_id' => $client->id,
                'type' => 'shipping',
                'label' => 'Офис',
                'is_default' => false,
                'first_name' => $this->faker->firstName,
                'last_name' => $this->faker->lastName,
                'company' => $this->faker->company,
                    'street' => $this->faker->streetAddress,
                    'city' => $this->faker->city,
                    'state' => $this->faker->state,
                    'postal_code' => $this->faker->postcode,
                    'country' => 'Russia',
                    'phone' => $this->generateRussianPhone(),
            ]);

            // Адрес оплаты
            ClientAddress::create([
                'client_id' => $client->id,
                    'type' => 'billing',
                'label' => 'Юридический адрес',
                'is_default' => true,
                'first_name' => $this->faker->firstName,
                'last_name' => $this->faker->lastName,
                'company' => $this->faker->company,
                'street' => $this->faker->streetAddress,
                'city' => $this->faker->city,
                'state' => $this->faker->state,
                'postal_code' => $this->faker->postcode,
                'country' => 'Russia',
                'phone' => $this->generateRussianPhone(),
            ]);

            // Иногда добавляем дачу
            if ($this->faker->boolean(30)) {
                ClientAddress::create([
                    'client_id' => $client->id,
                    'type' => 'shipping',
                    'label' => 'Дача',
                    'is_default' => false,
                    'first_name' => $this->faker->firstName,
                    'last_name' => $this->faker->lastName,
                    'company' => null,
                    'street' => $this->faker->streetAddress,
                    'city' => $this->faker->city,
                    'state' => $this->faker->state,
                    'postal_code' => $this->faker->postcode,
                    'country' => 'Russia',
                    'phone' => $this->generateRussianPhone(),
        ]);
            }
        });
    }
}
