<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class MerchantOrderHistory extends Model
{


    protected $table = 'merchant_order_history';

    protected $fillable = [
        'order_id',
        'merchant_order_id',
        'merchant_id',
        'status',
        'description',
    ];
}
