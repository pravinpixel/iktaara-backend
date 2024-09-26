<?php

namespace App\Models\Product;

use App\Models\GlobalSettings;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class ProductMetaTag extends Model
{
    use HasFactory;

    protected $appends = ['logo'];

    protected $fillable = [
        'product_id',
        'meta_title',
        'meta_keyword',
        'meta_description',
    ];

    public function getLogoAttribute(){
        $product = Product::where('id', $this->product_id)->first();
        $global_settings = GlobalSettings::first();
        if($product){
            $imagePath = $product->image_sm;
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
