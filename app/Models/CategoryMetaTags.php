<?php

namespace App\Models;

use App\Models\Product\ProductCategory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class CategoryMetaTags extends Model
{
    use HasFactory;
    protected $fillable = [
        'category_id',
        'meta_title',
        'meta_keyword',
        'meta_description'
    ];

     protected $appends = ['logo'];

    public function product_category()
    {
        return $this->hasOne(ProductCategory::class, 'id', 'category_id');
    }

    public function getLogoAttribute(){
        $product_category = ProductCategory::where('id', $this->category_id)->first();
        $global_settings = GlobalSettings::first();
        if($product_category){
            $imagePath = $product_category->image_sm;
            if (!Storage::exists($imagePath)) {
                $path               = asset($global_settings->logo);
            } else {
                $url                = Storage::url($imagePath);
                $path               = asset($url);
            }
        }else{
            $path               = asset($global_settings->logo);
        }
        return $path;
    }
}
