<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Rebajes_pza extends Model
{
    use HasFactory;
    protected $table = 'rebajes_pza';

    protected $fillable = [
        'id_pza',
        'id_proceso	',
        'n_juego',
        'rebajes1',
        'rebajes2',
        'rebajes3',
        'profundidad_bordonio',
        'vena1',
        'vena2',
        'simetria',
        'error',
        'observaciones',
    ];
}
