<?php

namespace Database\Seeders;

use App\Models\Cargo;
use App\Models\User;
use Illuminate\Database\Seeder;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $cargoAdmin = Cargo::where('codigo', 'admin')->first();

        User::updateOrCreate(
            ['cpf' => '111.111.111-11'],
            [
                'cargo_id' => $cargoAdmin?->id,
                'name' => 'Administrador',
                'usuario' => 'admin',
                'numero' => '(11) 99999-9999',
                'email' => 'admin@vianorte.com',
                'password' => '123456',
                'active' => true,
            ]
        );

        User::updateOrCreate(
            ['cpf' => '061.375.065-92'],
            [
                'cargo_id' => $cargoAdmin?->id,
                'name' => 'Eduardo Andrade',
                'usuario' => 'eduardo',
                'numero' => '(79) 99898-5298',
                'email' => 'eduardudu927@gmail.com',
                'password' => 'dudu2305',
                'active' => true,
            ]
        );
    }
}