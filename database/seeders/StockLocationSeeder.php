<?php

namespace Database\Seeders;

use App\Models\StockLocation;
use Illuminate\Database\Seeder;

class StockLocationSeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            ['name' => 'Rota', 'slug' => 'rota'],
            ['name' => 'Almoxarifado', 'slug' => 'almoxarifado'],
        ];

        foreach ($items as $item) {
            StockLocation::updateOrCreate(
                ['slug' => $item['slug']],
                [
                    'name' => $item['name'],
                    'active' => true,
                ]
            );
        }
    }
}