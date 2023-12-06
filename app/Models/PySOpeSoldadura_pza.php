<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PySOpeSoldadura_pza extends Model
{
    use HasFactory;
    protected $table = 'pysopesoldadura_pza';

    protected $fillable = [
        'id_pza',
        'id_proceso	',
        'n_pieza',
        'altura',
        'alturaCandado1',
        'alturaCandado2',
        'alturaAsientoObturador1',
        'alturaAsientoObturador2',
        'profundidadSoldadura1',
        'profundidadSoldadura2',
        'pushUp',
        'observaciones',
    ];
}
