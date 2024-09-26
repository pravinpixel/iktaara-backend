<?php

namespace App\Models\Master;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class DynamicBrandCategory extends Model
{
    use HasFactory;


    protected $fillable = [
        'category_name',
        'link',
        'image',
        'status',
        'sort_order'
    ];

    public function getImageAttribute(){
        if(isset($this->attributes['image'])){
            // if(!Storage::exists('brands/'.$this->attributes['id'].'/default'.$this->attributes['brand_logo'])){
            //     return asset('assets/logo/no-img-1.png');
            // }else{
                return asset(Storage::url('brand_category_banner/'.$this->attributes['id'].'/'.$this->attributes['image']));
            // }
        }else{
            return asset('assets/logo/no-img-1.png');
        }
    }
}
