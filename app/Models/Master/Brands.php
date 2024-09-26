<?php

namespace App\Models\Master;

use App\Models\Product\Product;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;


class Brands extends Model
{
    use HasFactory, SoftDeletes;

    protected $appends = ['promo_banner_1', 'promo_banner_2'];
    protected $fillable = [
        'brand_name',
        'brand_logo',
        'slug',
        'brand_banner',
        'short_description',
        'notes',
        'order_by',
        'added_by',
        'status',
        'profit_margin_percent',
        'promo_banner_1',
        'promo_banner_2',
        'faq_content'
    ];

    public function products()
    {
        return $this->hasMany(Product::class, 'brand_id', 'id');
    }

    public function category()
    {
        return $this->hasMany(Product::class, 'brand_id', 'id')
            ->selectRaw('p.*, mm_products.id as product_id')
            ->join('product_categories', 'product_categories.id', '=', 'products.category_id')
            ->join(DB::raw('mm_product_categories as p'), DB::raw('p.id'), '=', 'product_categories.parent_id')
            ->where('products.status', 'published')
            ->groupBy(DB::raw('p.id'));
    }

    public function scopeSubCategory($category_id)
    {
        return $this->hasMany(Product::class, 'brand_id', 'id')
            ->selectRaw('product_categories.*')
            ->join('product_categories', 'product_categories.id', '=', 'products.category_id')
            ->where('product_categories.parent_id', $category_id)
            ->groupBy(DB::raw('p.id'));
    }

    public function getBrandLogoAttribute()
    {
        if (isset($this->attributes['brand_logo'])) {
            // if(!Storage::exists('brands/'.$this->attributes['id'].'/default'.$this->attributes['brand_logo'])){
            //     return asset('assets/logo/no-img-1.png');
            // }else{
            return asset(Storage::url('brands/' . $this->attributes['id'] . "/default/" . $this->attributes['brand_logo']));
            // }
        } else {
            return asset('assets/logo/no-img-1.png');
        }
    }

    public function getBrandBannerAttribute()
    {
        if (isset($this->attributes['brand_banner'])  && (!empty($this->attributes['brand_banner']))) {
            $bannerImagePath        = 'brands/' . $this->attributes['id'] . '/banner/' . $this->attributes['brand_banner'];
            $url                    = Storage::url($bannerImagePath);
            $banner_path            = asset($url);
            return $banner_path;
        } else {
            return asset('assets/logo/no_img_category_md.jpg');
        }
    }

    public function getPromoBanner1Attribute()
    {
        if (isset($this->attributes['promo_banner_1']) && (!empty($this->attributes['promo_banner_1']))) {
            $bannerImagePath        = 'brands/' . $this->attributes['id'] . '/promo_banner_1/' . $this->attributes['promo_banner_1'];
            $url                    = Storage::url($bannerImagePath);
            $banner_path            = asset($url);
            return $banner_path;
        } else {
            return asset('assets/logo/no_img_category_md.jpg');
        }
    }

    public function getPromoBanner2Attribute()
    {
        if (isset($this->attributes['promo_banner_2']) && (!empty($this->attributes['promo_banner_1']))) {

            $bannerImagePath        = 'brands/' . $this->attributes['id'] . '/promo_banner_2/' . $this->attributes['promo_banner_2'];
            $url                    = Storage::url($bannerImagePath);
            $banner_path            = asset($url);
            return $banner_path;
        } else {
            return asset('assets/logo/no_img_category_md.jpg');
        }
    }


    public function associatedCategories()
    {
        return $this->hasMany(BrandCategory::class, 'brand_id', 'id');
    }
}
