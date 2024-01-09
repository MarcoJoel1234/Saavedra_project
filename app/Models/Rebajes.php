<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Rebajes extends Model
{
    use HasFactory;
    protected $table = 'rebajes';
    protected $fillable = [
        'id',
        'id_ot'
    ];
}
