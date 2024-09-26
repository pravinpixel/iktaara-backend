<?php

namespace App\Models\Product;

use App\Models\CategoryMetaTags;
use App\Models\Settings\Tax;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class ProductCategory extends Model
{
    use HasFactory,SoftDeletes;
    protected $appends = ['banner'];
    protected $fillable = [
        'name',
        'parent_id',
        'slug',
        'description',
        'image',
        'image_sm',
        'image_md',
        'is_featured',
        'status',
        'order_by',
        'added_by',
        'tag_line',
        'tax_id',
        'is_home_menu',
        'is_instrumental_category',
        'updated_by',
        'profit_margin_percent',
        'faq_content',
        'category_banner'
    ];

    public function meta()
    {
        return $this->hasOne(CategoryMetaTags::class, 'category_id', 'id');
    }

    public function tax()
    {
        return $this->hasOne(Tax::class, 'id', 'tax_id');
    }

    public function userInfo()
    {
        return $this->hasOne(User::class, 'id', 'added_by');
    }

    public function parent()
    {
        return $this->belongsTo(ProductCategory::class, 'parent_id', 'id');
    }

    public function childCategory()
    {
        return $this->hasMany(ProductCategory::class, 'parent_id', 'id')->where('status', 'published')->orderBy('order_by', 'asc');
    }

    public function childTopMenuCategory()
    {
        return $this->hasMany(ProductCategory::class, 'parent_id', 'id')->select('id','name','is_featured', 'slug')->where(['status' => 'published', 'is_home_menu' => 'yes'])->orderBy('order_by', 'asc');
    }

    public function getBannerAttribute(){
        if (isset($this->attributes['category_banner']) && (!empty($this->attributes['category_banner']))) {
            $bannerImagePath        = 'productCategory/' . $this->id . '/banner/' . $this->attributes['category_banner'];
            $url                    = Storage::url($bannerImagePath);
            $banner_path            = asset($url);
            return $banner_path;
        } else {
            return asset('assets/logo/no_img_category_md.jpg');
        }
    }

}
