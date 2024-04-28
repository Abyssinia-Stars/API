<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Work extends Model
{
    use HasFactory;


    protected $fillable = ['title','client_id', 'catagory', 'description', 'status', 'from_date','to_date'];
    


    public function Users()
    {
        return $this->belongsTo('App\Models\MezmurModel\User');
    }
}