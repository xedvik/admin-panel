<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

// Интерфейсы
use App\Contracts\Repositories\BaseRepositoryInterface;
use App\Contracts\Repositories\UserRepositoryInterface;
use App\Contracts\Repositories\OrderRepositoryInterface;
use App\Contracts\Repositories\ProductRepositoryInterface;
use App\Contracts\Repositories\ClientRepositoryInterface;
use App\Contracts\Repositories\ClientAddressRepositoryInterface;
use App\Contracts\Repositories\CategoryRepositoryInterface;
use App\Contracts\Repositories\OrderItemRepositoryInterface;
use App\Contracts\Repositories\SettingRepositoryInterface;
use App\Contracts\Repositories\ProductAttributeRepositoryInterface;
use App\Contracts\Repositories\ProductAttributeValueRepositoryInterface;
use App\Contracts\Repositories\CityRepositoryInterface;

// Реализации
use App\Repositories\UserRepository;
use App\Repositories\OrderRepository;
use App\Repositories\ProductRepository;
use App\Repositories\ClientRepository;
use App\Repositories\ClientAddressRepository;
use App\Repositories\CategoryRepository;
use App\Repositories\OrderItemRepository;
use App\Repositories\SettingRepository;
use App\Repositories\ProductAttributeRepository;
use App\Repositories\ProductAttributeValueRepository;
use App\Repositories\PromotionRepository;
use App\Contracts\Repositories\PromotionRepositoryInterface;
use App\Repositories\CityRepository;

// Модели
use App\Models\User;
use App\Models\Order;
use App\Models\Product;
use App\Models\Client;
use App\Models\ClientAddress;
use App\Models\Category;
use App\Models\OrderItem;
use App\Models\Setting;
use App\Models\ProductAttribute;
use App\Models\ProductAttributeValue;
use App\Models\City;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Регистрация сервисов
     */
    public function register(): void
    {
        // Регистрация репозиториев с их интерфейсами
        $this->app->bind(UserRepositoryInterface::class, function ($app) {
            return new UserRepository($app->make(User::class));
        });

        $this->app->bind(OrderRepositoryInterface::class, function ($app) {
            return new OrderRepository($app->make(Order::class));
        });

        $this->app->bind(ProductRepositoryInterface::class, function ($app) {
            return new ProductRepository(
                $app->make(Product::class),
                $app->make(ProductAttributeValueRepositoryInterface::class)
            );
        });

        $this->app->bind(ClientRepositoryInterface::class, function ($app) {
            return new ClientRepository($app->make(Client::class));
        });

        $this->app->bind(CategoryRepositoryInterface::class, function ($app) {
            return new CategoryRepository($app->make(Category::class));
        });

        $this->app->bind(OrderItemRepositoryInterface::class, function ($app) {
            return new OrderItemRepository($app->make(OrderItem::class));
        });

        $this->app->bind(SettingRepositoryInterface::class, function ($app) {
            return new SettingRepository($app->make(Setting::class));
        });

        $this->app->bind(ProductAttributeRepositoryInterface::class, function ($app) {
            return new ProductAttributeRepository($app->make(ProductAttribute::class));
        });

        $this->app->bind(ProductAttributeValueRepositoryInterface::class, function ($app) {
            return new ProductAttributeValueRepository(
                $app->make(ProductAttributeValue::class),
                $app->make(\App\Services\Business\AttributeTypeService::class)
            );
        });

        $this->app->bind(ClientAddressRepositoryInterface::class, function ($app) {
            return new ClientAddressRepository($app->make(ClientAddress::class));
        });

        $this->app->bind(PromotionRepositoryInterface::class, PromotionRepository::class);

        $this->app->bind(CityRepositoryInterface::class, function ($app) {
            return new CityRepository($app->make(City::class));
        });
    }

    /**
     * Загрузка сервисов
     */
    public function boot(): void
    {
        //
    }
}
