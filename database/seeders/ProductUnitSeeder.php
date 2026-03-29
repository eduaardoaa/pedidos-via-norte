<?php

namespace Database\Seeders;

use App\Models\ProductUnit;
use Illuminate\Database\Seeder;

class ProductUnitSeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            ['name' => 'Unidade', 'abbreviation' => 'un'],
            ['name' => 'Litro', 'abbreviation' => 'l'],
            ['name' => 'Quilograma', 'abbreviation' => 'kg'],
            ['name' => 'Grama', 'abbreviation' => 'g'],
            ['name' => 'Metro', 'abbreviation' => 'm'],
            ['name' => 'Rolo', 'abbreviation' => 'rolo'],
            ['name' => 'Caixa', 'abbreviation' => 'cx'],
            ['name' => 'Pacote', 'abbreviation' => 'pct'],
            ['name' => 'Par', 'abbreviation' => 'par'],
        ];

        foreach ($items as $item) {
            ProductUnit::updateOrCreate(
                ['abbreviation' => $item['abbreviation']],
                [
                    'name' => $item['name'],
                    'active' => true,
                ]
            );
        }
    }
}