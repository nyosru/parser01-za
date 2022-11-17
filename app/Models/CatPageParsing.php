<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CatPageParsing extends Model
{
    use HasFactory;

    protected $fillable = [
        'cat_uri',
        'page',
        'status'
    ];
}
