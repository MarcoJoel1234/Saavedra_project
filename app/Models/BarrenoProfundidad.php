<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BarrenoProfundidad extends Model
{
    use HasFactory;
    protected $table = 'barrenoProfundidad';
    protected $fillable = [
        'id',
        'id_ot'
    ];
}
