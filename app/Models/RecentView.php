<?php

namespace App\Models;

use App\Models\Product\Product;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
class RecentView extends Model
{
    use HasFactory;

    protected $fillable = [

        'customer_id',
        'product_id',
        'guest_token'

    ];

    public function products()
    {
        return $this->hasOne(Product::class, 'id', 'product_id');
    }
}
