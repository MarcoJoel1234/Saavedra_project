<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Soldadura extends Model
{
    protected $table = 'soldadura';
    protected $fillable = [
        'id',
        'id_ot'
    ];
}
