<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PiecesCepilladoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $registros = [];

        // 1M - 36M y 1H - 36H
        for ($i = 1; $i <= 36; $i++) {
            $registros[] = $this->makeRegistro($i . 'M');
            $registros[] = $this->makeRegistro($i . 'H');
        }

        // 66M - 80M y 66H - 80H
        for ($i = 66; $i <= 80; $i++) {
            $registros[] = $this->makeRegistro($i . 'M');
            $registros[] = $this->makeRegistro($i . 'H');
        }

        // Realizar upsert según la clave única ['id_clase', 'n_pieza', 'proceso']
        DB::table('piezas')->upsert(
            $registros,
            ['id_clase', 'n_pieza', 'proceso'], // claves únicas
            [
                'id_ot',
                'maquina',
                'id_operador',
                'error',
                'liberacion',
                'updated_at'
            ] // columnas que se actualizarán
        );
    }

    private function makeRegistro(string $n_pieza): array
    {
        return [
            'id_ot' => 6507,
            'id_clase' => 3,
            'maquina' => 2,
            'id_operador' => 5141,
            'proceso' => 'Cepillado',
            'error' => 'Ninguno',
            'liberacion' => 0,
            'n_pieza' => $n_pieza,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ];
    }
}
