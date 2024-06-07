<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EventBlogs extends Model
{
    use HasFactory;

    protected $fillable=[
        'user_id', // 'user_id' is required
        'title',
        'description',
        'event_date',
        'price',
        'location',
        'organizer_name',
        'image'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}