<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cepillado extends Model
{
    protected $table = 'cepillado';
    protected $fillable = [
        'id',
        'id_ot'
    ];
}
