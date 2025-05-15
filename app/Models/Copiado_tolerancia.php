<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Copiado_tolerancia extends Model
{
    use HasFactory;
    protected $table = 'copiado_tolerancia';
    protected $fillable = [
        'id_proceso',
        'diametro1_cilindrado',
        'profundidad1_cilindrado',
        'diametro2_cilindrado',
        'profundidad2_cilindrado',
        'diametro_sufridera',
        'diametro_ranura',
        'profundidad_ranura',
        'profundidad_sufridera',
        'altura_total',
        'diametro1_cavidades',
        'profundidad1_cavidades',
        'diametro2_cavidades',
        'profundidad2_cavidades',
        'diametro3',
        'profundidad3',
        'diametro4',
        'profundidad4',
        'volumen'
    ];
}
