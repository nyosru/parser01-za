<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Good extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'cat-id',
        'name',
        'uri',
        'img',
        'discount',
        'opis',
        'price',
        'price-old',
        'articul',
        'kod',
        'load-type'
    ];

    protected $dates = ['deleted_at'];
}
