<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Offer extends Model
{
    use HasFactory;



    protected $fillable = ['work_id','client_id','artist_id', 'status', 'price', 'offer_point_required'];


    public function Jobs()
    {
        return $this->belongsTo('App\Models\Work');
    }

    public function Users()
    {
        return $this->belongsTo('App\Models\User');
    }


}