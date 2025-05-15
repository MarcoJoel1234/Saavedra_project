<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cepillado_cnominal extends Model
{
    use HasFactory;
    protected $table = 'cepillado_cnominal';
    protected $fillable = [
        'id_proceso',
        'radiof_mordaza',
        'radiof_mayor',
        'radiof_sufridera',
        'profuFinal_CFC	',
        'profuFinal_mitadMB',
        'profuFinal_PCO',
        'ensamble',
        'distancia_barrenoAli1',
        'distancia_barrenoAli2',
        'profu_barrenoAli1',
        'profu_barrenoAli2',
        'altura_vena1',
        'altura_vena2',
        'ancho_vena1',
        'ancho_vena2',
        'pin1',
        'pin2',
    ];
}
