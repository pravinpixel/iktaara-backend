<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderRejectReason extends Model
{
    use HasFactory;

    protected $table = 'merchant_order_reject_reasons';

    protected $fillable = [
        'reason'
    ];
}
