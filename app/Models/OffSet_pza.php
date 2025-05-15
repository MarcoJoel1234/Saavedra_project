<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OffSet_pza extends Model
{
    use HasFactory;
    protected $table = 'offSet_pza';
    protected $fillable = [
        'id_pza',
        'id_proceso	',
        'n_juego',
        'anchoRanura',
        'profuTaconHembra',
        'profuTaconMacho',
        'simetriaHembra',
        'simetriaMacho',
        'anchoTacon',
        'barrenoLateralHembra',
        'barrenoLateralMacho',
        'alturaTaconInicial',
        'alturaTaconIntermedia',
        'observaciones',
        'error'
    ];
}
