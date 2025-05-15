<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Soldadura_pza extends Model
{
    use HasFactory;
    protected $table = 'soldadura_pza';

    protected $fillable = [
        'id_pza',
        'id_proceso	',
        'n_pieza',
        'pesoxpieza',
        'tiempo_precalentado',
        'temperatura_precalentado',
        'tiempo_aplicacion',
        'tipo_soldadura',
        'lote',
        'observaciones',
    ];
}
