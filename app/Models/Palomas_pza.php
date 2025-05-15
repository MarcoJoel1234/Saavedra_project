<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Palomas_pza extends Model
{
    use HasFactory;
    protected $table = 'palomas_pza';
    protected $fillable = [
        'id_pza',
        'id_proceso	',
        'n_juego',
        'anchoPaloma',
        'gruesoPaloma',
        'profundidadPaloma',
        'rebajeLlanta',
        'observaciones',
        'error'
    ];
}
