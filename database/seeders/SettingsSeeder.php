<?php

namespace Database\Seeders;

use App\Services\Seeders\SettingsSeederService;
use Illuminate\Database\Seeder;

class SettingsSeeder extends Seeder
{
    public function __construct(
        private SettingsSeederService $seederService
    ) {}

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->seederService->createDefaultSettings();
    }
}
