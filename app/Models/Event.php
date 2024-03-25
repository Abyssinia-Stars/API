<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    use HasFactory;


    protected $fillable = ['end', 'start', 'artist_id', 'is_availabile'];


    protected $hidden = [
        'created_at',
        'updated_at',
    ];



    public function Users()
    {
        return $this->belongsTo('App\Models\MezmurModel\User');
    }
}