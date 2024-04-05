<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Offer extends Model
{
    use HasFactory;



    protected $fillable = ['job_id','client_id','artist_id', 'status', 'price'];


    public function Jobs()
    {
        return $this->belongsTo('App\Models\Job');
    }

    public function Users()
    {
        return $this->belongsTo('App\Models\User');
    }


}