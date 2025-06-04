<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

// Business Services
use App\Services\Business\AttributeTypeService;
use App\Services\Business\ClientAddressService;

// Form Factories
use App\Services\Forms\AttributeFormFieldFactory;
use App\Services\Forms\CategoryFormFieldFactory;
use App\Services\Forms\ClientFormFieldFactory;
use App\Services\Forms\OrderFormFieldFactory;
use App\Services\Forms\OrderForClientFormFieldFactory;
use App\Services\Forms\OrderItemFormFieldFactory;
use App\Services\Forms\ProductFormFieldFactory;
use App\Services\Forms\ProductAttributeFormFieldFactory;
use App\Services\Forms\ProductInCategoryFormFieldFactory;

// Table Factories
use App\Services\Tables\CategoryTableElementsFactory;
use App\Services\Tables\ClientTableElementsFactory;
use App\Services\Tables\OrderTableElementsFactory;
use App\Services\Tables\OrderForClientTableElementsFactory;
use App\Services\Tables\OrderItemTableElementsFactory;
use App\Services\Tables\ProductTableComponentsFactory;
use App\Services\Tables\ProductAttributeTableComponentsFactory;
use App\Services\Tables\ProductInCategoryTableElementsFactory;

// Seeder Services
use App\Services\Seeders\DatabaseSeederService;
use App\Services\Seeders\ProductAttributeSeederService;
use App\Services\Seeders\SettingsSeederService;

// Repository Interfaces
use App\Contracts\Repositories\ClientRepositoryInterface;
use App\Contracts\Repositories\CategoryRepositoryInterface;
use App\Contracts\Repositories\ProductRepositoryInterface;
use App\Contracts\Repositories\OrderRepositoryInterface;
use App\Contracts\Repositories\OrderItemRepositoryInterface;
use App\Contracts\Repositories\ProductAttributeRepositoryInterface;
use App\Contracts\Repositories\SettingRepositoryInterface;
use App\Contracts\Repositories\ClientAddressRepositoryInterface;
use App\Contracts\Repositories\ProductAttributeValueRepositoryInterface;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Business Services
        $this->registerBusinessServices();

        // Form Factories
        $this->registerFormFactories();

        // Table Factories
        $this->registerTableFactories();

        // Seeder Services
        $this->registerSeederServices();
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }

    /**
     * Регистрация бизнес-сервисов
     */
    private function registerBusinessServices(): void
    {
        $this->app->singleton(AttributeTypeService::class);

        $this->app->singleton(ClientAddressService::class, function ($app) {
            return new ClientAddressService(
                $app->make(ClientAddressRepositoryInterface::class)
            );
        });
    }

    /**
     * Регистрация фабрик форм
     */
    private function registerFormFactories(): void
    {
        // Attribute Form Factory
        $this->app->singleton(AttributeFormFieldFactory::class, function ($app) {
            return new AttributeFormFieldFactory(
                $app->make(AttributeTypeService::class)
            );
        });

        // Category Form Factory
        $this->app->singleton(CategoryFormFieldFactory::class);

        // Client Form Factory
        $this->app->singleton(ClientFormFieldFactory::class);

        // Order Form Factories
        $this->app->singleton(OrderFormFieldFactory::class, function ($app) {
            return new OrderFormFieldFactory(
                $app->make(ClientAddressService::class)
            );
        });

        $this->app->singleton(OrderForClientFormFieldFactory::class);
        $this->app->singleton(OrderItemFormFieldFactory::class, function ($app) {
            return new OrderItemFormFieldFactory(
                $app->make(ProductRepositoryInterface::class),
                $app->make(ProductAttributeValueRepositoryInterface::class)
            );
        });

        // Product Form Factories
        $this->app->singleton(ProductFormFieldFactory::class, function ($app) {
            return new ProductFormFieldFactory(
                $app->make(AttributeFormFieldFactory::class)
            );
        });

        $this->app->singleton(ProductAttributeFormFieldFactory::class, function ($app) {
            return new ProductAttributeFormFieldFactory(
                $app->make(AttributeTypeService::class)
            );
        });

        $this->app->singleton(ProductInCategoryFormFieldFactory::class);
    }

    /**
     * Регистрация фабрик таблиц
     */
    private function registerTableFactories(): void
    {
        // Category Table Factory
        $this->app->singleton(CategoryTableElementsFactory::class, function ($app) {
            return new CategoryTableElementsFactory(
                $app->make(CategoryRepositoryInterface::class)
            );
        });

        // Client Table Factory
        $this->app->singleton(ClientTableElementsFactory::class, function ($app) {
            return new ClientTableElementsFactory(
                $app->make(ClientRepositoryInterface::class)
            );
        });

        // Order Table Factories
        $this->app->singleton(OrderTableElementsFactory::class, function ($app) {
            return new OrderTableElementsFactory(
                $app->make(OrderRepositoryInterface::class),
                $app->make(OrderFormFieldFactory::class)
            );
        });

        $this->app->singleton(OrderForClientTableElementsFactory::class);
        $this->app->singleton(OrderItemTableElementsFactory::class, function ($app) {
            return new OrderItemTableElementsFactory(
                $app->make(ProductRepositoryInterface::class)
            );
        });

        // Product Table Factories
        $this->app->singleton(ProductTableComponentsFactory::class, function ($app) {
            return new ProductTableComponentsFactory(
                $app->make(ProductRepositoryInterface::class)
            );
        });

        $this->app->singleton(ProductAttributeTableComponentsFactory::class, function ($app) {
            return new ProductAttributeTableComponentsFactory(
                $app->make(AttributeTypeService::class)
            );
        });

        $this->app->singleton(ProductInCategoryTableElementsFactory::class);
    }

    /**
     * Регистрация сервисов для сидеров
     */
    private function registerSeederServices(): void
    {
        $this->app->singleton(DatabaseSeederService::class, function ($app) {
            return new DatabaseSeederService(
                $app->make(ClientRepositoryInterface::class),
                $app->make(CategoryRepositoryInterface::class),
                $app->make(ProductRepositoryInterface::class),
                $app->make(OrderRepositoryInterface::class),
                $app->make(OrderItemRepositoryInterface::class)
            );
        });

        $this->app->singleton(ProductAttributeSeederService::class, function ($app) {
            return new ProductAttributeSeederService(
                $app->make(ProductAttributeRepositoryInterface::class)
            );
        });

        $this->app->singleton(SettingsSeederService::class, function ($app) {
            return new SettingsSeederService(
                $app->make(SettingRepositoryInterface::class)
            );
        });
    }
}
