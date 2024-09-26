<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;


class Banner extends Model
{
    use HasFactory, SoftDeletes;
    protected $appends = ['image'];

    protected $fillable = [
        'title',
        'description',
        'banner_image',
        'mobile_banner',
        'links',
        'tag_line',
        'order_by',
        'status',
        'added_by',
        'banner_type'
    ];

    public function getImageAttribute()
    {
        if (isset($this->banner_image) && !empty($this->banner_image)) {
            $bannerImagePath        = 'banner/' . $this->id . '/main_banner/' . $this->banner_image;
            $url                    = Storage::url($bannerImagePath);
            return asset($url);
        } else {
            return '';
        }
    }
}
