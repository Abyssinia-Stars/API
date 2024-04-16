<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentInfo extends Model
{
    use HasFactory;

    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'currency',
        'phone_number',
        'user_id',
        'account_name',
        'account_number',
        'bank_code'
    ];

    public function Users()
    {
        return $this->belongsTo(User::class);
    }
}
