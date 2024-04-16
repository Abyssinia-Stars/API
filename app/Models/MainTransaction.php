<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MainTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 
        'client_id',
        'artist_id',
        'full_amount',
        'our_amount',
        'after_tax',
        'tax_percentage',
        'net_amount',
        'percentage'

    ];

    // Define the relationship with the User model
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
