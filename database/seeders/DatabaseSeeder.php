<?php

namespace Database\Seeders;

use App\Services\Seeders\DatabaseSeederService;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function __construct(
        private DatabaseSeederService $seederService
    ) {}

    /**
     * Заполнить базу данных тестовыми данными.
     */
    public function run(): void
    {
        // Создаем базовые настройки
        $this->call(SettingsSeeder::class);
        $this->call(ProductAttributeSeeder::class);

        // Создаем админа
        $this->seederService->createAdmin();

        // Создаем корневые категории
        $this->seederService->createRootCategories();

        // Создаем товары для категорий
        $this->seederService->createProducts();

        // Создаем акции
        $this->call(PromotionSeeder::class);

        // Создаем статусы клиентов
        $this->call(ClientStatusSeeder::class);

        // Создаем клиентов
        $clients = $this->seederService->createClients();

        // Создаем заказы для клиентов
        $this->seederService->createOrdersForClients($clients);

        // Сидим города РФ
        $this->call(RussianCitiesSeeder::class);

        // Выводим статистику
        $stats = $this->seederService->getSeederStats();
        $this->command->info('База данных заполнена тестовыми данными!');
        $this->command->info('Создано:');
        $this->command->info('- Настроек: базовые настройки сайта');
        $this->command->info('- Категорий: ' . $stats['categories']);
        $this->command->info('- Товаров: ' . $stats['products']);
        $this->command->info('- Акций: ' . $stats['promotions']);
        $this->command->info('- Клиентов: ' . $stats['clients']);
        $this->command->info('- Заказов: ' . $stats['orders']);
        $this->command->info('- Позиций заказов: ' . $stats['order_items']);
    }
}

