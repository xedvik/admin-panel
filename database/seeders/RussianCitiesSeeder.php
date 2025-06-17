<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\City;

class RussianCitiesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $json = file_get_contents(storage_path('app/cities/russia-cities.json'));
        $citiesArr = json_decode($json, true);

        if (!is_array($citiesArr)) {
            echo "Ошибка: не удалось декодировать russia-cities.json\n";
            return;
        }

        $count = 0;
        foreach ($citiesArr as $city) {
            City::updateOrCreate([
                'id' => $city['id'],
            ], [
                'name' => $city['name'],
                'region' => $city['region']['name'] ?? null,
                'latitude' => $city['coords']['lat'] ?? null,
                'longitude' => $city['coords']['lon'] ?? null,
            ]);
            $count++;
        }
        echo "Добавлено городов: $count\n";
    }
}
