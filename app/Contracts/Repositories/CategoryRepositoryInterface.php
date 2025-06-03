<?php

namespace App\Contracts\Repositories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Collection;

interface CategoryRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * Получить только активные категории
     */
    public function getActive(): Collection;

    /**
     * Получить только корневые категории
     */
    public function getRoot(): Collection;

    /**
     * Найти категорию по slug
     */
    public function findBySlug(string $slug): ?Category;

    /**
     * Получить дочерние категории
     */
    public function getChildren(int $parentId): Collection;

    /**
     * Получить категории с их товарами
     */
    public function getWithProducts(): Collection;

    /**
     * Получить категории с количеством товаров
     */
    public function getWithProductsCount(): Collection;
}
