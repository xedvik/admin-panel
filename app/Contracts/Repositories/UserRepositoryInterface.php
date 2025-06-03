<?php

namespace App\Contracts\Repositories;

use App\Models\User;

interface UserRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * Найти пользователя по email
     */
    public function findByEmail(string $email): ?User;

    /**
     * Получить всех администраторов
     */
    public function getAdmins();

    /**
     * Проверить может ли пользователь получить доступ к панели
     */
    public function canAccessPanel(int $userId): bool;
}
