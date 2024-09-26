<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class BannerResource extends JsonResource
{

    public function toArray($request)
    {
        if(isset($this->banner_image) && !empty($this->banner_image)){
            $bannerImagePath        = 'banner/' . $this->id . '/main_banner/' . $this->banner_image;
            $url                    = Storage::url($bannerImagePath);
            $path                   = asset($url);
        }else{
            $path = '';
        }

        if (isset($this->mobile_banner) && !empty($this->mobile_banner)) {
            $mobileBanner           = 'banner/' . $this->id . '/mobile_banner/' . $this->mobile_banner;
            $mobUrl                 = Storage::url($mobileBanner);
            $pathBanner             = asset($mobUrl);
        } else {
            $pathBanner = '';
        }


        $tmp['id']            = $this->id;
        $tmp['title']         = $this->title;
        $tmp['image']         = $path;
        $tmp['mobile_banner'] = $pathBanner;
        $tmp['links']           = $this->links;
        $tmp['description']   = $this->description;
        $tmp['tag_line']      = $this->tag_line;

        return $tmp;
    }
}
