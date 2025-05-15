<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BarrenoProfundidad_cnominal extends Model
{
    use HasFactory;
    protected $table = 'barrenoProfundidad_cnominal';
    protected $fillable = [
        'id_proceso',
        'broca1',
        'tiempo1',
        'broca2',
        'tiempo2',
        'broca3',
        'tiempo3',         
        'entradaSalida',
        'diametro_arrastre1',
        'diametro_arrastre2',
        'diametro_arrastre3',
    ];
}
