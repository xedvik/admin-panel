<?php

namespace App\Services\Seeders;

use App\Contracts\Repositories\ClientRepositoryInterface;
use App\Contracts\Repositories\CategoryRepositoryInterface;
use App\Contracts\Repositories\ProductRepositoryInterface;
use App\Contracts\Repositories\OrderRepositoryInterface;
use App\Contracts\Repositories\OrderItemRepositoryInterface;
use App\Models\User;
use App\Models\Category;
use App\Models\Client;
use App\Models\Product;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Promotion;
use App\Models\ClientStatus;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Collection;

class DatabaseSeederService
{
    public function __construct(
        private ClientRepositoryInterface $clientRepository,
        private CategoryRepositoryInterface $categoryRepository,
        private ProductRepositoryInterface $productRepository,
        private OrderRepositoryInterface $orderRepository,
        private OrderItemRepositoryInterface $orderItemRepository
    ) {}

    /**
     * Создать администратора
     */
    public function createAdmin(): User
    {
        return User::factory()->create([
            'name' => 'Admin',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
        ]);
    }

    /**
     * Создать корневые категории
     */
    public function createRootCategories(): Collection
    {
        $rootCategoryNames = [
            'Электроника',
            'Одежда и обувь',
            'Дом и сад',
            'Красота и здоровье',
            'Спорт и отдых',
        ];

        $categories = collect();

        foreach ($rootCategoryNames as $categoryName) {
            $category = Category::factory()->active()->create([
                'name' => $categoryName,
                'slug' => \Illuminate\Support\Str::slug($categoryName),
            ]);
            $categories->push($category);

            // Создаем подкатегории
            Category::factory(3)->active()->create([
                'parent_id' => $category->id,
            ]);
        }

        return $categories;
    }

    /**
     * Создать товары для категорий
     */
    public function createProducts(): void
    {
        $allCategories = Category::all();

        $allCategories->each(function ($category) {
            Product::factory(rand(5, 15))
                ->active()
                ->inStock()
                ->create([
                    'category_id' => $category->id,
                ]);
        });

        // Создаем рекомендуемые товары
        Product::factory(10)->featured()->inStock()->create();
    }

    /**
     * Создать клиентов
     */
    public function createClients(): Collection
    {
        $clients = collect();

        $regularStatus = ClientStatus::where('name', 'regular')->first();

        // Обычные клиенты
        $regularClients = Client::factory(30)
            ->active()
            ->verified()
            ->create(['client_status_id' => $regularStatus?->id]);
        $clients = $clients->merge($regularClients);

        // Клиенты с несколькими адресами
        $multiAddressClients = Client::factory(20)
            ->active()
            ->verified()
            ->withMultipleAddresses()
            ->create(['client_status_id' => $regularStatus?->id]);
        $clients = $clients->merge($multiAddressClients);

        return $clients;
    }

    /**
     * Создать заказы для клиентов
     */
    public function createOrdersForClients(Collection $clients): void
    {
        $clients->each(function ($client) {
            $orderCount = rand(0, 5);

            for ($i = 0; $i < $orderCount; $i++) {
                $order = Order::factory()->create([
                    'client_id' => $client->id,
                ]);

                $this->createOrderItems($order);
                $this->updateOrderTotals($order);
            }
        });
    }

    /**
     * Создать позиции заказа
     */
    private function createOrderItems(Order $order): void
    {
        $itemCount = rand(1, 5);
        $products = Product::inRandomOrder()->take($itemCount)->get();

        foreach ($products as $product) {
            $quantity = rand(1, 3);
            OrderItem::factory()->create([
                'order_id' => $order->id,
                'product_id' => $product->id,
                'product_name' => $product->name,
                'product_sku' => $product->sku,
                'quantity' => $quantity,
                'product_price' => $product->price,
                'total_price' => $quantity * $product->price,
            ]);
        }
    }

    /**
     * Обновить суммы заказа
     */
    private function updateOrderTotals(Order $order): void
    {
        $subtotal = $order->orderItems()->sum('total_price');
        $taxAmount = (int)($subtotal * 0.2);
        $shippingAmount = rand(0, 500);
        $discountAmount = rand(0, (int)($subtotal * 0.1));
        $totalAmount = $subtotal + $taxAmount + $shippingAmount - $discountAmount;

        $order->update([
            'subtotal' => $subtotal,
            'tax_amount' => $taxAmount,
            'shipping_amount' => $shippingAmount,
            'discount_amount' => $discountAmount,
            'total_amount' => $totalAmount,
        ]);
    }

    /**
     * Получить статистику для отображения
     */
    public function getSeederStats(): array
    {
        return [
            'categories' => Category::count(),
            'products' => Product::count(),
            'clients' => Client::count(),
            'orders' => Order::count(),
            'order_items' => OrderItem::count(),
            'promotions' => Promotion::count(),
        ];
    }
}
