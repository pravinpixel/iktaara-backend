<?php

namespace App\Models;

use App\Models\Master\Customer;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MerchantOrderStatus extends Model
{
    use HasFactory;

       protected $fillable = [
        'order_status',
        'order_status_name',
    ];

}
