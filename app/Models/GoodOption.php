<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GoodOption extends Model
{
    use HasFactory;

    protected $fillable = [
        'good-id',
        'param',
        'param-ed-izm',
        'value',
        'value-int'
    ];

}
