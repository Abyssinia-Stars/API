<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ArtistProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'bio',
        'category',
        'youtube_links',
        'attachments',
    ];



    protected $casts = [
        'category' => 'array',
        'youtube_links' => 'array',
        'attachments' => 'array',
    ];



}