<?php

namespace App\Models\Offers;

use App\Models\Product\Product;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CouponExcludedProducts extends Model
{
    use HasFactory;

    protected $fillable = [
        'coupon_id',
        'product_id',
        'quantity',
    ];

    public function products()
    {
        return $this->hasOne(Product::class, 'id', 'product_id');
    }
}
