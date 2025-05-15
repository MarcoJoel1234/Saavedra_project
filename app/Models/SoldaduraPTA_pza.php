<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SoldaduraPTA_pza extends Model
{
    use HasFactory;
    protected $table = 'soldaduraPTA_pza';

    protected $fillable = [
        'id_pza',
        'id_proceso	',
        'n_pieza',
        'temp_calentado',
        'temp_dispositivo',
        'limpieza',
        'observaciones',
    ];
}
