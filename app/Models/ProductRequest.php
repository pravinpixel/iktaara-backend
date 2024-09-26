<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;


class ProductRequest extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'product_name',
        'brand_name',
        'brand_model_code',
        'mrp',
        'seller_sku',
        'available_quantity',
        'description',
    ];
}
