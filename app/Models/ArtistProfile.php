<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ArtistProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'bio',
        'catagory',
        'attachments',
        'user_id'
    ];


    public function setCatagoryAttribute($value)
    {
        $this->attributes['catagory'] = json_encode($value);
    }

    // Define accessor for 'catagory' attribute
    public function getCatagoryAttribute($value)
    {
        return json_decode($value, true);
    }

    // Define mutator for 'attachments' attribute
    public function setAttachmentsAttribute($value)
    {
        $this->attributes['attachments'] = json_encode($value);
    }

    // Define accessor for 'attachments' attribute
    public function getAttachmentsAttribute($value)
    {
        return json_decode($value, true);
    }
}