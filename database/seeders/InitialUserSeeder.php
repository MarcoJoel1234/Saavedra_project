<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class InitialUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::firstOrCreate(
            ['matricula' => '12345'],
            [
                'nombre' => 'Master',
                'a_paterno' => 'GIS',
                'a_materno' => 'Saavedra',
                'contrasena' => bcrypt('12345678'),
                'perfil' => 3,
            ]
        );
    }
}
