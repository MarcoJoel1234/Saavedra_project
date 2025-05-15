<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmbudoCM extends Model
{
    use HasFactory;
    protected $table = 'embudoCM';
    protected $fillable = [
        'id',
        'id_ot'
    ];
}
