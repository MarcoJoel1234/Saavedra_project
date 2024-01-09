<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OffSet extends Model
{
    use HasFactory;
    protected $table = 'offSet';
    protected $fillable = [
        'id',
        'id_ot'
    ];
}
