<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RevLaterales_tolerancia extends Model
{
    use HasFactory;
    protected $table = 'revlaterales_tolerancia';
    
    protected $fillable = [
        'id_proceso',
        'desfasamiento_entrada1',
        'desfasamiento_entrada2',
        'desfasamiento_salida1',
        'desfasamiento_salida2',
        'ancho_simetriaEntrada1',
        'ancho_simetriaEntrada2',
        'ancho_simetriaSalida1',
        'ancho_simetriaSalida2',
        'angulo_corte1',
        'angulo_corte2',
    ];
}
