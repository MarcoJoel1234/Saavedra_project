<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Asentado extends Model
{
    use HasFactory;
    protected $table = 'asentado';
    protected $fillable = [
        'id',
        'id_ot'
    ];
}
