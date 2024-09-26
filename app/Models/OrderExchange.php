<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderExchange extends Model
{
    use HasFactory;

    protected $table = 'order_exchange';

    public const EXCHANGE_STATUS = [
        0 => 'pending',
        1 => 'approved',
        2 => 'Rejected',
    ];

    protected $fillable = [
        'customer_id',
        'order_item_id',
        'product_id',
        'order_id',
        'seller_id',
        'delivered_at',
        'quantity',
        'created_at',
        'updated_at',
        'deleted_at',
        'reason',
        'reason_id',
        'status',
        'approved_by',
    ];
}
