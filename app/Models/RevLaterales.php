<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RevLaterales extends Model
{
    use HasFactory;
    protected $table = 'revlaterales';
    protected $fillable = [
        'id',
        'id_ot'
    ];
}
