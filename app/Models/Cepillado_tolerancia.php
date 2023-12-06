<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cepillado_tolerancia extends Model
{
    use HasFactory;
    protected $table = 'cepillado_tolerancia';
    
    protected $fillable = [
        'id_proceso',
        'radiof_mordaza1',
        'radiof_mordaza2',
        'radiof_mayor1',
        'radiof_mayor2',
        'radiof_sufridera1',
        'radiof_sufridera2',
        'profuFinal_CFC1',
        'profuFinal_CFC2',
        'profuFinal_mitadMB1',
        'profuFinal_mitadMB2',
        'profuFinal_PCO1',
        'profuFinal_PCO2',
        'ensamble1',
        'ensamble2',
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
