<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Desbaste_tolerancia extends Model
{
    use HasFactory;
    protected $table = 'desbaste_tolerancia';
    
    protected $fillable = [
        'id_proceso',
        'diametro_mordaza1',
        'diametro_mordaza2',
        'diametro_ceja1',
        'diametro_ceja2',
        'diametro_sufrideraExtra1',
        'diametro_sufrideraExtra2',
        'simetria_ceja1',
        'simetria_ceja2',
        'simetria_mordaza1',
        'simetria_mordaza2',
        'altura_ceja1',
        'altura_ceja1',
        'altura_sufridera1',
        'altura_sufridera2',
    ];
}
 