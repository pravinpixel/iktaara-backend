<?php

namespace App\Models\Seller;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MerchantProfit extends Model
{
    use HasFactory;

    protected $fillable = [
        'merchant_id',
        'brand_id',
        'category_id',
        'brand_margin_value',
        'category_margin_value'
    ];  
}
