<?php

namespace App\Traits;

use App\Contracts\Repositories\ProductRepositoryInterface;
use App\Contracts\Repositories\ProductAttributeRepositoryInterface;
use App\Contracts\Repositories\ProductAttributeValueRepositoryInterface;
use App\Contracts\Repositories\CategoryRepositoryInterface;

trait UsesRepositories
{
    protected function productRepository(): ProductRepositoryInterface
    {
        return app(ProductRepositoryInterface::class);
    }

    protected function attributeRepository(): ProductAttributeRepositoryInterface
    {
        return app(ProductAttributeRepositoryInterface::class);
    }

    protected function attributeValueRepository(): ProductAttributeValueRepositoryInterface
    {
        return app(ProductAttributeValueRepositoryInterface::class);
    }

    protected function categoryRepository(): CategoryRepositoryInterface
    {
        return app(CategoryRepositoryInterface::class);
    }
}