<?php

namespace App\Models\Seller;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MerchantShopsData extends Model
{
    use HasFactory;

    protected $fillable = [
        'merchant_id',
        'shop_name',
        'contact_person',
        'contact_number',
        'state_id',
        'area_id',
        'pincode_id'
    ];
       
}
