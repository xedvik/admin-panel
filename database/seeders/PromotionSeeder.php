<?php

namespace Database\Seeders;

use App\Services\Seeders\PromotionSeederService;
use Illuminate\Database\Seeder;

class PromotionSeeder extends Seeder
{
    public function __construct(
        private PromotionSeederService $seederService
    ) {}

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->seederService->createPromotions();
    }
}
