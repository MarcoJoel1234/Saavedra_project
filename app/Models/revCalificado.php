<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class revCalificado extends Model
{
    use HasFactory;
    protected $table = 'revCalificado';
    protected $fillable = [
        'id',
        'id_ot'
    ];
}

