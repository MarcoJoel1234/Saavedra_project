<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Rectificado_pza extends Model
{
    use HasFactory;
    protected $table = 'rectificado_pza';

    protected $fillable = [
        'id_pza',
        'id_proceso	',
        'n_juego',
        'cumple',
        'observaciones',
    ];
}
