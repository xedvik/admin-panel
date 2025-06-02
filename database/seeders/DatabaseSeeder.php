<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\Category;
use App\Models\Client;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;

class DatabaseSeeder extends Seeder
{
    /**
     * Заполнить базу данных тестовыми данными.
     */
    public function run(): void
    {
        // Создаем админа
        User::factory()->create([
            'name' => 'Admin',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
        ]);

        // Создаем корневые категории
        $rootCategories = [
            'Электроника',
            'Одежда и обувь',
            'Дом и сад',
            'Красота и здоровье',
            'Спорт и отдых',
        ];

        $categories = collect(); // коллекция категорий
        foreach ($rootCategories as $categoryName) {
            $category = Category::factory()->active()->create([
                'name' => $categoryName, // название категории
                'slug' => \Illuminate\Support\Str::slug($categoryName), // slug категории
            ]);
            $categories->push($category); // добавляем категорию в коллекцию

            // Создаем подкатегории для каждой корневой категории
            Category::factory(3)->active()->create([
                'parent_id' => $category->id,
            ]);
        }

        // Получаем все категории (включая подкатегории)
        $allCategories = Category::all();

        // Создаем товары для каждой категории
        $allCategories->each(function ($category) {
            Product::factory(rand(5, 15))
                ->active()
                ->inStock()
                ->create([
                    'category_id' => $category->id,
                ]);
        });

        // Создаем несколько рекомендуемых товаров
        Product::factory(10)->featured()->inStock()->create();

        // Создаем клиентов
        $clients = Client::factory(50)
            ->active()
            ->verified()
            ->create();

        // Создаем заказы для клиентов
        $clients->each(function ($client) {
            // Каждый клиент может иметь от 0 до 5 заказов
            $orderCount = rand(0, 5);

            for ($i = 0; $i < $orderCount; $i++) {
                $order = Order::factory()->create([
                    'client_id' => $client->id,
                ]);

                // Добавляем товары в заказ (от 1 до 5 позиций)
                $itemCount = rand(1, 5);
                $products = Product::inRandomOrder()->take($itemCount)->get();

                $subtotal = 0; // сумма заказа без скидок и доставки
                foreach ($products as $product) {
                    $quantity = rand(1, 3); // количество товара
                    $orderItem = OrderItem::factory()->create([
                        'order_id' => $order->id,
                        'product_id' => $product->id,
                        'product_name' => $product->name,
                        'product_sku' => $product->sku,
                        'quantity' => $quantity,
                        'product_price' => $product->price,
                        'total_price' => $quantity * $product->price,
                    ]);

                    $subtotal += $orderItem->total_price; // сумма заказа без скидок и доставки
                }

                // Обновляем суммы заказа
                $taxAmount = $subtotal * 0.2;
                $shippingAmount = rand(0, 500);
                $discountAmount = rand(0, $subtotal * 0.1);
                $totalAmount = $subtotal + $taxAmount + $shippingAmount - $discountAmount; // сумма заказа

                $order->update([
                    'subtotal' => $subtotal,
                    'tax_amount' => $taxAmount,
                    'shipping_amount' => $shippingAmount,
                    'discount_amount' => $discountAmount,
                    'total_amount' => $totalAmount,
                ]);
            }
        });

        $this->command->info('База данных заполнена тестовыми данными!');
        $this->command->info('Создано:');
        $this->command->info('- Категорий: ' . Category::count());
        $this->command->info('- Товаров: ' . Product::count());
        $this->command->info('- Клиентов: ' . Client::count());
        $this->command->info('- Заказов: ' . Order::count());
        $this->command->info('- Позиций заказов: ' . OrderItem::count());
    }
}
