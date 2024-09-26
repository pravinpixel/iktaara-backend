<?php

namespace App\Models\Seller;

use App\Models\MerchantProduct;
use App\Models\Product\Product;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Merchant extends Authenticatable
{
    use HasFactory;

    protected $fillable = [

        'first_name',
        'last_name',
        'email',
        'password',
        'merchant_no',
        'mobile_no',
        'address',
        'city',
        'area_id',
        'state_id',
        'pincode_id',
        'desc',
        'terms_conditions',
        'mode'
         // 'remember_token',
        // 'verification_token',
        // 'forgot_token',
        // 'profile_image',
        // 'status',
        // 'verification_pending',
    ];

    public function state(){
        return $this->belongsTo(State::class, 'state_id', 'id');
    }

    public function area(){
        return $this->belongsTo(Area::class, 'area_id', 'id');
    }

    public function pincode(){
        return $this->belongsTo(Pincode::class, 'pincode_id', 'id');
    }

    public function shop(){
        return $this->belongsTo(MerchantShopsData::class, 'merchant_id', 'id');
    }

    public function merchantProducts()
    {
        return $this->hasMany(MerchantProduct::class, 'id', 'merchant_id');
    }

     /**
     * brand have the high priority value
     */
    public static function getProfitMargin($product_id, $merchant_id, $mrp){
        $product = Product::find($product_id);
        $parent_category_id   = $product->productCategory->parent->id ?? '';
        $brand_id      = $product->productBrand->id ?? '';

        $brand_profit_margin = MerchantProfit::where([['merchant_id', $merchant_id],['brand_id', $brand_id]])->first();
        $profit_margin = isset($brand_profit_margin) ? $brand_profit_margin->brand_margin_value : null;
        if(!$brand_profit_margin){
            $category_profit_margin = MerchantProfit::where([['merchant_id', $merchant_id],['category_id', $parent_category_id]])->first();
            if($category_profit_margin){
                $profit_margin = isset($category_profit_margin) ? $category_profit_margin->category_margin_value : null;
            }
        }

        return round((($profit_margin/100) * $mrp), 2);
    }

    public static function getMerchantNo($merchant_id){
        $merchant_name = '';
        if($merchant_id){
            $merchant = Merchant::find($merchant_id);
            if($merchant){
                $merchant_name = $merchant->merchant_no;
            }
        }


        return $merchant_name;
    }

    public static function getMerchantName($merchant_id){
        $merchant_name = '';
        if($merchant_id){
            $merchant = Merchant::find($merchant_id);
            if($merchant){
                $merchant_name = $merchant->first_name.' '.$merchant->last_name;
            }
        }


        return $merchant_name;
    }

    public static function getMerchantLocation($merchant_id){
        $area_name = '';
        if($merchant_id){
            $merchant = Merchant::find($merchant_id);
            if($merchant){
                $merchant_area_id = $merchant->area_id;
                $area = Area::find($merchant_area_id);
                $area_name = $area->area_name;
            }
        }


        return $area_name;
    }

    /**
     * brand have the high priority value
     */
    public static function getProfitMarginPercentage($product_id, $merchant_id, $mrp){
        $product = Product::find($product_id);
        $parent_category_id  = $product->productCategory->parent->id ?? '';
        $brand_id = $product->productBrand->id ?? '';

        $brand_profit_margin = MerchantProfit::where([['merchant_id', $merchant_id],['brand_id', $brand_id]])->first();
        $profit_margin = isset($brand_profit_margin) ? $brand_profit_margin->brand_margin_value : null;
        if(!$brand_profit_margin){
            $category_profit_margin = MerchantProfit::where([['merchant_id', $merchant_id],['category_id', $parent_category_id]])->first();
            if($category_profit_margin){
                $profit_margin = isset($category_profit_margin) ? $category_profit_margin->category_margin_value : null;
            }
        }

        return $profit_margin;

    }
}
