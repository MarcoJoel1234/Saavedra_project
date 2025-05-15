<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Palomas_tolerancia extends Model
{
    use HasFactory;
    protected $table = 'palomas_tolerancia';
    protected $fillable = [
        'id_proceso',
        'anchoPaloma',
        'gruesoPaloma',
        'profundidadPaloma',
        'rebajeLlanta',
    ];
}
