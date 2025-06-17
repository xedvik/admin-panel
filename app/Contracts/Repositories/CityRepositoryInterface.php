<?php

namespace App\Contracts\Repositories;

use App\Contracts\Repositories\BaseRepositoryInterface;

interface CityRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * Получить массив названий городов по id
     * @param array $ids
     * @return array [id => name]
     */
    public function getNamesByIds(array $ids): array;

    /**
     * Получить массив городов для селекта
     * @return array [id => name]
     */
    public function getOptionsForSelect(): array;
}
