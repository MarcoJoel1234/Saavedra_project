<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DesbasteExterior extends Model
{
    use HasFactory;
    protected $table = 'desbasteexterior';
    protected $fillable = [
        'id',
        'id_ot'
    ];
}
