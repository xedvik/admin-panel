<?php

namespace App\Repositories;

use App\Contracts\Repositories\CategoryRepositoryInterface;
use App\Models\Category;
use Illuminate\Database\Eloquent\Collection;

class CategoryRepository extends BaseRepository implements CategoryRepositoryInterface
{
    public function __construct(Category $model)
    {
        parent::__construct($model);
    }

    public function getActive(): Collection
    {
        return $this->model->where('is_active', true)->get();
    }

    public function getRoot(): Collection
    {
        return $this->model->whereNull('parent_id')->orderBy('sort_order')->get();
    }

    public function findBySlug(string $slug): ?Category
    {
        return $this->model->where('slug', $slug)->first();
    }

    public function getChildren(int $parentId): Collection
    {
        return $this->model->where('parent_id', $parentId)->orderBy('sort_order')->get();
    }

    public function getWithProducts(): Collection
    {
        return $this->model->with('products')->get();
    }

    public function getWithProductsCount(): Collection
    {
        return $this->model->withCount('products')->get();
    }

    /**
     * Получить активные корневые категории
     */
    // public function getActiveRoot(): Collection
    // {
    //     return $this->model->where('is_active', true)
    //                       ->whereNull('parent_id')
    //                       ->orderBy('sort_order')
    //                       ->get();
    // }

    /**
     * Получить категории с их дочерними категориями
     */
    // public function getWithChildren(): Collection
    // {
    //     return $this->model->with('children')->get();
    // }

    /**
     * Получить полное дерево категорий
     */
    // public function getCategoryTree(): Collection
    // {
    //     return $this->model->with('children.children')->whereNull('parent_id')->orderBy('sort_order')->get();
    // }
}
