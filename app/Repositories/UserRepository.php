<?php

namespace App\Repositories;

use App\Contracts\Repositories\UserRepositoryInterface;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

class UserRepository extends BaseRepository implements UserRepositoryInterface
{
    public function __construct(User $model)
    {
        parent::__construct($model);
    }

    public function findByEmail(string $email): ?User
    {
        return $this->model->where('email', $email)->first();
    }

    public function getAdmins(): Collection
    {
        return $this->model->where('role', 'admin')->get();
    }

    public function canAccessPanel(int $userId): bool
    {
        $user = $this->find($userId);
        return $user && $user->role === 'admin';
    }
}
