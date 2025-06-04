<?php

namespace Database\Seeders;

use App\Services\Seeders\ProductAttributeSeederService;
use Illuminate\Database\Seeder;

class ProductAttributeSeeder extends Seeder
{
    public function __construct(
        private ProductAttributeSeederService $seederService
    ) {}

    /**
     * Run the database seeder.
     */
    public function run(): void
    {
        $this->seederService->createDefaultAttributes();
    }
}
