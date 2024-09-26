<?php

namespace App\Models;

use App\Models\MerchantProduct as ModelsMerchantProduct;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Seller\Merchant;

class MerchantProduct extends Model
{
    use HasFactory;

    protected $table = 'merchant_products';

    protected $fillable = [
        'merchant_id',
        'product_id',
        'qty',
        'price',
        'status'
    ];

    public static function getProductAssignedToMerchant($product_id){
        $data = MerchantProduct::where([['product_id',$product_id], ['merchant_id',Auth()->user()->id]])->first();
        return ($data != null) ? true : false;
    }

    public function merchant()
    {
        return $this->belongsTo(Merchant::class, 'merchant_id');
    }
}
