<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Venues extends Model
{
    use HasFactory;

    protected $fillable=[
        'name',
        'location',
        'capacity',
        'price',
        'image',
        'phone',
        'email',
        'map'
    ];
}