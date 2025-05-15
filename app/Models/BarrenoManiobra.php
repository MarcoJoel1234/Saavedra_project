<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BarrenoManiobra extends Model
{
    use HasFactory;
    protected $table = 'barrenomaniobra';
    protected $fillable = [
        'id',
        'id_ot'
    ];
}
