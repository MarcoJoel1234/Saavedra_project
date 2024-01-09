<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Palomas extends Model
{
    use HasFactory;
    protected $table = 'palomas';
    protected $fillable = [
        'id',
        'id_ot'
    ];
}
