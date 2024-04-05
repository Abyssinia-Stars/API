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
        'youtube_links',
        'attachments',
        'role'
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
}
