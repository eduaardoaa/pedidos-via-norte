<?php

namespace Database\Seeders;

use App\Models\Cargo;
use Illuminate\Database\Seeder;

class CargoSeeder extends Seeder
{
    public function run(): void
    {
        Cargo::updateOrCreate(
            ['codigo' => 'admin'],
            [
                'nome' => 'Administrador',
                'ativo' => true,
            ]
        );

        Cargo::updateOrCreate(
            ['codigo' => 'funcionario'],
            [
                'nome' => 'Funcionário',
                'ativo' => true,
            ]
        );
    }
}