<?php

namespace App\Http\Resources;

use App\Models\Master\Brands;
use App\Models\Product\ProductCategory;
use Illuminate\Http\Resources\Json\JsonResource;

class MenuAllResource extends JsonResource
{
    public function toArray($request)
    {

        $childTmp = $childTmpinnerChild = [];
        $tmp[ 'id' ]        = $this->id;
        $tmp[ 'name' ]      = $this->name;
        $tmp[ 'slug' ]      = $this->slug;


        if( isset( $this->childCategory ) && !empty( $this->childCategory ) ) {
            foreach ($this->childCategory as $child ) {
                $childLevelCategory = ProductCategory::where('status', 'published')->where('parent_id', $child->id)->get();
                $childTmpinnerChild = [];
                if(isset($childLevelCategory) && !empty( $childLevelCategory ) ){
                    foreach($childLevelCategory as $childLevel){
                        $childTmp1['id']    = $childLevel->id;
                        $childTmp1['name'] = $childLevel->name;
                        $childTmp1['slug']   = $childLevel->slug;
                        $childTmp1['breadcrum_slug']   = str_replace(" ", "-", strtolower($childLevel->name));
                        $childTmpinnerChild[]         = $childTmp1;
                    }
                    $innerTmp['id']     = $child->id;
                    $innerTmp['name']   = $child->name;
                    $innerTmp['slug']   = $child->slug;
                    $innerTmp['breadcrum_slug']   = str_replace(" ", "-", strtolower($child->name));
                    $innerTmp['innerchild'] = $childTmpinnerChild;
                    $childTmp[]         = $innerTmp;
                }

            }
            // $childTmp['inner_child'][] = $childTmpinnerChild;
        }
        $tmp['child']       = $childTmp;
        // $tmp['child']['inner_child'][]       = $childTmpinnerChild;
        if($this->slug == 'shop-by-brand'){
            $brand_data = BrandResource::collection(Brands::select('id', 'brand_name', 'slug')->where(['status' => 'published'])->orderBy('order_by', 'asc')->get());
            foreach($brand_data as $data){
                $tmp['child'][]     = ['id' => $data->id, 'name' => $data->brand_name, 'slug' => $data->slug];
            }
        }
        return $tmp;
    }
}
