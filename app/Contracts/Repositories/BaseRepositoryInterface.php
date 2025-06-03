<?php

namespace App\Contracts\Repositories;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;

interface BaseRepositoryInterface
{
    /**
     * Получить все записи
     */
    public function all(): Collection;

    /**
     * Найти запись по ID
     */
    public function find(int $id): ?Model;

    /**
     * Найти запись по ID или выбросить исключение
     */
    public function findOrFail(int $id): Model;

    /**
     * Создать новую запись
     */
    public function create(array $data): Model;

    /**
     * Обновить запись
     */
    public function update(int $id, array $data): Model;

    /**
     * Обновить или создать запись
     */
    public function updateOrCreate(array $attributes, array $values = []): Model;

    /**
     * Удалить запись
     */
    public function delete(int $id): bool;

    /**
     * Удалить записи по условию
     */
    public function deleteWhere(array $conditions): int;

    /**
     * Получить записи с пагинацией
     */
    public function paginate(int $perPage = 15): LengthAwarePaginator;

    /**
     * Найти записи по условию
     */
    public function where(array $conditions): Collection;

    /**
     * Найти первую запись по условию
     */
    public function whereFirst(array $conditions): ?Model;

    /**
     * Проверить существование записи по условию
     */
    public function exists(array $conditions): bool;

    /**
     * Получить количество записей
     */
    public function count(): int;

    /**
     * Получить количество записей по условию
     */
    public function countWhere(array $conditions): int;

    /**
     * Получить Builder для дополнительных условий
     */
    public function query(): Builder;
}