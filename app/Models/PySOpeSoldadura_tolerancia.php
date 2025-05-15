<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PySOpeSoldadura_tolerancia extends Model
{
    use HasFactory;
    protected $table = 'pysopesoldadura_tolerancia';
    
    protected $fillable = [
        'id_proceso',
        'altura',
        'alturaCandado1',
        'alturaCandado2',
        'alturaAsientoObturador1',
        'alturaAsientoObturador2',
        'profundidadSoldadura1',
        'profundidadSoldadura2',
        'pushUp',
    ];
}
