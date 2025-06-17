<?php

namespace App\Repositories;

use App\Contracts\Repositories\CityRepositoryInterface;
use App\Models\City;
use Illuminate\Database\Eloquent\Collection;

class CityRepository extends BaseRepository implements CityRepositoryInterface
{
    public function __construct(City $model)
    {
        parent::__construct($model);
    }

    /**
     * {@inheritdoc}
     */
    public function getNamesByIds(array $ids): array
    {
        return $this->model->whereIn('id', $ids)->pluck('name', 'id')->toArray();
    }

    /**
     * {@inheritdoc}
     */
    public function getOptionsForSelect(): array
    {
        return $this->model->orderBy('name')->pluck('name', 'id')->toArray();
    }
}
