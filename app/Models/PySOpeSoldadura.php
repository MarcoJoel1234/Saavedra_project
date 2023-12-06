<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PySOpeSoldadura extends Model
{
    use HasFactory;
    protected $table = 'pysopesoldadura';
    protected $fillable = [
        'id',
        'id_ot'
    ];
}
