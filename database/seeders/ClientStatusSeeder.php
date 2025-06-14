<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ClientStatus;

class ClientStatusSeeder extends Seeder
{
    public function run(): void
    {
        $statuses = [
            ['name' => 'new', 'label' => 'Новый'],
            ['name' => 'regular', 'label' => 'Обычный'],
            ['name' => 'loyal', 'label' => 'Постоянный'],
            ['name' => 'vip', 'label' => 'VIP'],
        ];

        foreach ($statuses as $status) {
            ClientStatus::firstOrCreate(['name' => $status['name']], $status);
        }
    }
}