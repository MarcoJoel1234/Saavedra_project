<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Copiado extends Model
{
    use HasFactory;
    protected $table = 'copiado';
    protected $fillable = [
        'id',
        'id_ot'
    ];
}
