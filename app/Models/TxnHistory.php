<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TxnHistory extends Model
{
    use HasFactory;
    protected $fillable = [
        'tx_ref',
        'amount',
        'from',
        'to',
        'reason',
        'type'
    ];
}
