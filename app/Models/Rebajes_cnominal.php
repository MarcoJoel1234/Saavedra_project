<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Rebajes_cnominal extends Model
{
    use HasFactory;
    protected $table = 'rebajes_cnominal';
    
    protected $fillable = [
        'id_proceso',
        'rebajes1',
        'rebajes2',
        'rebajes3',
        'profundidad_bordonio',
        'vena1',
        'vena2',
        'simetria',
    ];
}
