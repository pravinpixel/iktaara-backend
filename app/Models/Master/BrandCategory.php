<?php

namespace App\Models\Master;

use App\Models\Product\ProductCategory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class BrandCategory extends Model
{
    use HasFactory;

    protected $appends = ['category_name', 'category_image', 'category_slug'];

    protected $fillable = [
        'brand_id',
        'category_id',
        'image'
    ];

    public function getCategoryNameAttribute(){
        $product_category = ProductCategory::where('id', $this->category_id)->first();
        if($product_category){
        return $product_category->name;
        }

        return "";
    }

    public function getCategoryImageAttribute(){
        $product_category = ProductCategory::where('id', $this->category_id)->first();
        $imagePath = 'productCategory/' . $product_category->id . '/default/' . $product_category->image;
        if (!Storage::exists($imagePath)) {
            $path               = asset('assets/logo/no-img-1.png');
            $url = Storage::url($imagePath);
            $path = asset($url);
        } else {
            $url = Storage::url($imagePath);
            $path = asset($url);
        }

        return $path;
    }

    public function getCategorySlugAttribute(){
        $product_category = ProductCategory::where('id', $this->category_id)->first();
        if($product_category){
        return $product_category->slug;
        }

        return "";
    }
}
