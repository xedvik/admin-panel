<?php

namespace App\Repositories;

use App\Contracts\Repositories\BaseRepositoryInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;

abstract class BaseRepository implements BaseRepositoryInterface
{
    protected Model $model;

    public function __construct(Model $model)
    {
        $this->model = $model;
    }

    public function all(): Collection
    {
        return $this->model->all();
    }

    public function find(int $id): ?Model
    {
        return $this->model->find($id);
    }

    public function findOrFail(int $id): Model
    {
        return $this->model->findOrFail($id);
    }

    public function create(array $data): Model
    {
        return $this->model->create($data);
    }

    public function update(int $id, array $data): Model
    {
        $model = $this->findOrFail($id);
        $model->update($data);
        return $model->fresh();
    }

    public function updateOrCreate(array $attributes, array $values = []): Model
    {
        return $this->model->updateOrCreate($attributes, $values);
    }

    public function delete(int $id): bool
    {
        return $this->model->destroy($id);
    }

    public function deleteWhere(array $conditions): int
    {
        return $this->model->where($conditions)->delete();
    }

    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return $this->model->paginate($perPage);
    }

    public function where(array $conditions): Collection
    {
        return $this->model->where($conditions)->get();
    }

    public function whereFirst(array $conditions): ?Model
    {
        return $this->model->where($conditions)->first();
    }

    public function exists(array $conditions): bool
    {
        return $this->model->where($conditions)->exists();
    }

    public function count(): int
    {
        return $this->model->count();
    }

    public function countWhere(array $conditions): int
    {
        return $this->model->where($conditions)->count();
    }

    public function query(): Builder
    {
        return $this->model->newQuery();
    }
}
