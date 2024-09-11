<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Fecha_proceso extends Model
{
    public $timestamps = false;
    protected $table = 'fechas_procesos';

    protected $fillable = [
        'clase',
        'proceso',
        'fecha_inicio',
        'fecha_fin',
    ];
}
