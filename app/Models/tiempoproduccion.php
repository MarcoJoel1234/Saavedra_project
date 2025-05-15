<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class tiempoproduccion extends Model
{
    protected $table = 'tiempos_produccion';

    protected $fillable = [
        'clase',
        'tamanio',
        'proceso',
        'tiempo'
    ];
}
