<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cavidades extends Model
{
    use HasFactory;
    protected $table = 'cavidades';
    protected $fillable = [
        'id',
        'id_ot'
    ];
}
