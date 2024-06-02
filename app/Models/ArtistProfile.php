<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ArtistProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'bio',
        'category',
        'location',
        'gender',
        
        'youtube_links',
        'attachments',
        'role',
        'offfer_point',
        'price_rate',
        'is_subscribed',
        'manager_id'

    ];

    protected $casts = [
        'category' => 'array',
        'youtube_links' => 'array',
        'attachments' => 'array',
    ];

    protected $hidden =[
        'created_at',
        'updated_at'
    ];


    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function manager()
    {
        return $this->belongsTo(User::class, 'manager_id');
    }

}
