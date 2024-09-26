<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category\SubCategory;
use App\Models\CategoryMetaTags;
use App\Models\Master\Brands;
use App\Models\Product\Product;
use App\Models\Product\ProductAttributeSet;
use App\Models\Product\ProductCategory;
use App\Models\Product\ProductCollection;
use App\Models\Product\ProductWithAttributeSet;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class FilterController extends Controller
{
    public function getFilterStaticSideMenu()
    {

        // $product_availability = array(
        //     'in_stock' => 'In Stock',
        //     'coming_soon' => 'Coming Soon',
        // );

        $video_shopping         = array('video_shopping' => 'Video Shopping is available');

        $sory_by                = array(
            array('id' => null, 'name' => 'Featured', 'slug' => 'is_featured'),
            array('id' => null, 'name' => 'Price: High to Low', 'slug' => 'price_high_to_low'),
            array('id' => null, 'name' => 'Price: Low to High', 'slug' => 'price_low_to_high'),
        );

        $tags                   = SubCategory::select('sub_categories.id', 'sub_categories.name', 'sub_categories.slug')
            ->join('main_categories', 'main_categories.id', '=', 'sub_categories.parent_id')
            ->where('sub_categories.status', 'published')
            ->where('main_categories.slug', 'product-tags')
            ->orderBy('sub_categories.order_by', 'asc')
            ->get()->toArray();


        $labels                   = SubCategory::select('sub_categories.id', 'sub_categories.name', 'sub_categories.slug')
            ->join('main_categories', 'main_categories.id', '=', 'sub_categories.parent_id')
            ->where('sub_categories.status', 'published')
            ->where('main_categories.slug', 'product-labels')
            ->orderBy('sub_categories.order_by', 'asc')
            ->get()->toArray();
        // dd( $tags );
        // $sory_by                = array_merge($tags, $labels, $sory_by);
        // $sory_by                = array_merge($tags, $labels, $sory_by);

        // $discounts              = ProductCollection::select('id', 'collection_name', 'slug')
        //     ->where('can_map_discount', 'yes')
        //     ->where('status', 'published')
        //     ->orderBy('order_by', 'asc')
        //     ->get()->toArray();

        $discounts = ProductCollection::select('id', 'collection_name', 'slug')->where(['show_home_page' => 'yes', 'status' => 'published', 'can_map_discount' => 'yes'])
            ->orderBy('order_by', 'asc')->get()->toArray();


        $collection              = ProductCollection::select('id', 'collection_name', 'slug')
            ->where('can_map_discount', 'no')
            ->where('show_home_page', 'yes')
            ->where('status', 'published')
            ->orderBy('order_by', 'asc')
            ->get()->toArray();

        $response               = array(
            // 'product_availability' => $product_availability,
            'video_shopping' => $video_shopping,
            'sory_by' => $sory_by,
            'discounts' => $discounts
        );
        return new Response(array('error' => 0, 'status_code' => 200, 'message' => 'Data loaded successfully', 'status' => 'success', 'data' => $response), 200);
    }

    public function getProducts(Request $request)
    {

        $search_type = $request->search_type;
        $category_id = $request->category_id;
        $query = $request->search_field;
        $page                   = $request->page ?? 0;
        $filter_category        = ($request->category == "null") ? '' : $request->category;
        $filter_sub_category    = $request->scategory;
        $filter_category_slug    = $request->filter_category_slug;
        $filter_sub_sub_category    = $request->sscategory;
        $filter_availability    = $request->availability;
        $filter_brand           = $request->brand;
        $filter_discount        = $request->discount;
        $filter_collection      = $request->collection;
        $filter_attribute       = $request->attributes_category;
        $sort                   = $request->sort;
        $minimum_price          = $request->minimum_price;
        $maximum_price          = $request->maximum_price;
        $rating                 = $request->rating ?? 1;
        $filter_brand_category  = $request->brand_category;

        $filter_availability_array = [];
        $filter_attribute_array = [];
        $filter_brand_array = [];
        $filter_discount_array = [];
        $filter_collection_array = [];
        $filter_category_slug_array = $filter_category_info = [];
        $filter_brand_category_array = $brand_category_info = [];
        $filter_booking     = $request->booking;
        $filter_data = [];
        $filter_availability_text = [];
        $filter_attribute_text = [];
        $filter_brand_text = [];
        $filter_discount_text = [];
        $filter_collection_text = [];
        $filter_category_text = [];
        $filter_brand_category_text = [];

        if (isset($filter_attribute) && !empty($filter_attribute)) {

            $filter_attribute_array = explode("-", $filter_attribute);
            $filter_attribute_text =  array_map(function ($attribute) {
                return [
                    'key' => 'attribute',
                    'value' => ucwords(str_replace("-", " ", $attribute)),
                    'slug' => $attribute
                ];
            }, $filter_attribute_array);
        }
        if (isset($filter_availability) && !empty($filter_availability)) {
            $filter_availability_array = explode("-", $filter_availability);
            $filter_availability_text =  array_map(function ($attribute) {
                return [
                    'key' => 'availability',
                    'value' => ucwords(str_replace("_", " ", $attribute)),
                    'slug' => $attribute
                ];
            }, $filter_availability_array);
        }
        if (isset($filter_brand) && !empty($filter_brand)) {
            $filter_brand_array     = explode("_", $filter_brand);
            $filter_brand_text =  array_map(function ($attribute) {
                return [
                    'key' => 'brand',
                    'value' => ucwords(str_replace("-", " ", $attribute)),
                    'slug' => $attribute
                ];
            }, $filter_brand_array);
        }

        if (isset($filter_discount) && !empty($filter_discount)) {
            $filter_discount_array     = explode("_", $filter_discount);
            $filter_discount_text =  array_map(function ($attribute) {
                return [
                    'key' => 'discount',
                    'value' => ucwords($attribute),
                    'slug' => $attribute
                ];
            }, $filter_discount_array);
        }

        if (isset($filter_collection) && !empty($filter_collection)) {
            $filter_collection_array     = explode("_", $filter_collection);
            $filter_collection_text =  array_map(function ($attribute) {
                return [
                    'key' => 'collection',
                    'value' => ucwords($attribute),
                    'slug' => $attribute
                ];
            }, $filter_collection_array);
        }

        if (isset($filter_category_slug) && !empty($filter_category_slug)) {
            $filter_category_slug_array     = explode("_", $filter_category_slug);
            $filter_category_data = ProductCategory::whereIn('slug', $filter_category_slug_array)->where([['status', 'published']])->get();

            $filter_category_names =  $filter_category_data->pluck('name')->toArray();
            $filter_category_slugs = $filter_category_data->pluck('slug')->toArray();

            // Combine names and slugs into the desired format
            $filter_category_text = array_map(function ($name, $slug) {
                return [
                    'key' => 'filter_category_slug',
                    'value' => $name,
                    'slug' => $slug
                ];
            }, $filter_category_names, $filter_category_slugs);
            $filter_category_info = $filter_category_data->pluck('id')->toArray();
        }

        if (isset($filter_brand_category) && !empty($filter_brand_category)) {
            $filter_brand_category_array     = explode("_", $filter_brand_category);
            $filter_brand_text =  array_map(function ($attribute) {
                return [
                    'key' => 'brand_category',
                    'value' => ucwords(str_replace("-", " ", $attribute)),
                    'slug' => $attribute
                ];
            }, $filter_brand_category_array);
            $brand_category_info = ProductCategory::whereIn('slug', $filter_brand_category_array)->where([['status', 'published']])->get()->pluck('id')->toArray();
        }

        $category_info = ProductCategory::where([['slug', $filter_category], ['status', 'published']])->first();
        $cat_id = $category_info->id ?? '';

        if (!empty($cat_id)) {
            $subCategories = ProductCategory::select(['id', 'name'])->where(['parent_id' => $cat_id, 'is_home_menu' => 'yes', 'status' => 'published'])
                ->orderBy('order_by', 'asc')
                ->get();
            $meta = CategoryMetaTags::where('category_id', $cat_id)->first();
        }
        if ($category_id != '') {
            $category_ids = ProductCategory::select('id')->where('parent_id', $category_id)->get()->toArray();
        } else {
            $category_ids = null;
        }
        $productAttrNames = [];
        if (isset($filter_attribute_array) && !empty($filter_attribute_array)) {
            $productWithData = ProductWithAttributeSet::whereIn('id', $filter_attribute_array)->get();
            if (isset($productWithData) && !empty($productWithData)) {
                foreach ($productWithData as $attr) {
                    $productAttrNames[] = $attr->title;
                }
            }
        }
        $start = microtime(true);
        $limit = 24;
        $skip = (isset($page) && !empty($page)) ? ($page * $limit) : 0;

        $from   = 1 + ($page * $limit);


        $take_limit = $limit;

        $filter_data = array_merge(
            $filter_availability_text,
            $filter_attribute_text,
            $filter_brand_text,
            $filter_discount_text,
            $filter_collection_text,
            $filter_category_text,
            $filter_brand_category_text
        );

        $details_data = Product::select('products.*')->where('products.status', 'published')->with('productBrand')
            ->join('product_categories', function ($join) {
                $join->on('product_categories.id', '=', 'products.category_id');
                // $join->orOn('product_categories.parent_id', '=', 'products.category_id');
            })
            // ->whereHas('productCategory', function ($q) {
            //     $q->whereColumn('product_categories.id', 'products.category_id')
            //       ->orWhereColumn('product_categories.parent_id', 'products.category_id');
            // })

            ->when($category_ids != '', function ($q) use ($category_ids) {
                $q->where(function ($q) use ($category_ids) {
                    return $q->whereIN('products.category_id', $category_ids);
                });
            })
            ->when($query != '', function ($q) use ($query) {
                $regexPattern = $query . '|' . substr($query, 0, -1); // seas|sea
                return $q->whereRaw("product_name REGEXP ?", [$regexPattern])->orderByRaw("product_name LIKE ? DESC", ["%{$query}%"]);
                // return $q->where('product_name', 'like', "%{$query}%");
                // ->orWhere('sku', 'like', "%{$query}%");
            })
            ->when($filter_category != '', function ($q) use ($cat_id) {
                $q->where(function ($query) use ($cat_id) {
                    return $query->where('product_categories.id', $cat_id)->orWhere('product_categories.parent_id', $cat_id);
                });
            })
            ->when($filter_sub_category != '', function ($q) use ($filter_sub_category) {
                return $q->where('product_categories.slug', $filter_sub_category);
            })
            ->when($filter_sub_sub_category != '', function ($q) use ($filter_sub_sub_category) {
                return $q->where('product_categories.slug', $filter_sub_sub_category);
            })
            ->when($filter_brand_category != '', function ($q) use ($brand_category_info) {
                return $q->whereIn('product_categories.id', $brand_category_info)->orWhereIn('product_categories.parent_id', $brand_category_info);
            })
            ->when($filter_category_slug != '', function ($q) use ($filter_category_info) {
                return $q->whereIn('product_categories.id', $filter_category_info)->orWhereIn('product_categories.parent_id', $filter_category_info);
            })
            ->when($filter_availability != '', function ($q) use ($filter_availability_array) {
                return $q->whereIn('products.stock_status', $filter_availability_array);
            })
            ->when($filter_brand != '', function ($q) use ($filter_brand_array) {
                return $q->whereHas('productBrand', function ($q1) use ($filter_brand_array) {
                    $q1->whereIn('slug', $filter_brand_array);
                });
            })
            ->when($filter_booking == 'video_shopping', function ($q) {
                return $q->where('products.has_video_shopping', 'yes');
            })
            ->when($filter_discount != '', function ($q) use ($filter_discount_array) {
                $q->join('product_collections_products', 'product_collections_products.product_id', '=', 'products.id');
                $q->join('product_collections', 'product_collections.id', '=', 'product_collections_products.product_collection_id');
                return $q->addSelect('collection_name')->whereIn('product_collections.slug', $filter_discount_array);
            })
            ->when($filter_collection != '', function ($q) use ($filter_collection_array) {
                $q->join('product_collections_products', 'product_collections_products.product_id', '=', 'products.id');
                $q->join('product_collections', 'product_collections.id', '=', 'product_collections_products.product_collection_id');
                return $q->addSelect('collection_name')->whereIn('product_collections.slug', $filter_collection_array);
            })
            ->when($filter_attribute != '', function ($q) use ($productAttrNames) {
                $q->join('product_with_attribute_sets', 'product_with_attribute_sets.product_id', '=', 'products.id');
                return $q->whereIn('product_with_attribute_sets.title', $productAttrNames);
            })
            ->when((isset($minimum_price) && isset($maximum_price)), function ($q) use ($minimum_price, $maximum_price) {
                return $q->whereBetween('products.mrp', [$minimum_price, $maximum_price]);
            })
            ->when((isset($request->rating)), function ($q) use ($rating) {
                $q->join('reviews', 'reviews.product_id', '=', 'products.id');
                return $q->where('reviews.star', '>=', $rating)->where('reviews.status', 1);
            })
            ->when($sort == 'price_high_to_low', function ($q) {
                $q->orderBy('products.mrp', 'desc');
            })
            ->when($sort == 'price_low_to_high', function ($q) {
                $q->orderBy('products.mrp', 'asc');
            })
            ->when($sort == 'is_featured', function ($q) {
                $q->orderBy('products.is_featured', 'desc');
            })
            ->where('products.stock_status', '!=', 'out_of_stock')
            ->groupBy('products.id');
        // ->skip(0)
        // ->paginate($limit, ['*'], 'page', $page);
        // ->take($take_limit)
        // ->get();
        $total = count($details_data->get());
        $details = $details_data->paginate($limit, ['*'], 'page', $page);
        // dd($details);
        if (isset($details) && !empty($details)) {
            $tmp = [];
            if ($request->scategory) {
                $meta            = (isset($details[0])) ? $details[0]->productCategory->meta : [];
            }
            $collection_name = (isset($details[0])) ? $details[0]->collection_name : '';
            foreach ($details as $items) {

                $category               = $items->productCategory;

                $salePrices             = getProductPrice($items);

                $pro                    = [];

                $pro['id']              = $items->id;
                $pro['product_name']    = $items->product_name;
                $pro['description']     = Str::of($items->description)->limit(100);
                $pro['category_name']   = $category->name ?? '';
                $pro['brand_name']      = $items->productBrand->brand_name ?? '';
                $pro['hsn_code']        = $items->hsn_code;
                $pro['product_url']     = $items->product_url;
                $pro['sku']             = $items->sku;
                $pro['has_video_shopping'] = $items->has_video_shopping;
                $pro['stock_status']    = $items->stock_status;
                $pro['is_featured']     = $items->is_featured;
                $pro['is_best_selling'] = $items->is_best_selling;
                $pro['is_new']          = $items->is_new;
                $pro['sale_prices']     = $salePrices;
                $pro['mrp_price']       = $items->mrp;
                $pro['image']           = $items->base_image;
                $pro['max_quantity']    = $items->quantity;
                $imagePath              = $items->base_image;

                if (!Storage::exists($imagePath)) {
                    $path               = asset('assets/logo/product-noimg.png');
                } else {
                    $url                = Storage::url($imagePath);
                    $path               = asset($url);
                }

                $pro['image']           = $path;

                $tmp[] = $pro;
            }
        }

        // if ($total < $limit) {
        //     $to = $total;
        // }

        if ($request->category_id) {
            $product_category_info = ProductCategory::select('faq_content', 'name')->where('parent_id', $category_id)->first();
            $category_name = isset($product_category_info) ? $product_category_info->name : null;
            $faq_content = isset($product_category_info) ? $product_category_info->faq_content : null;
        } elseif ($request->sscategory) {
            $product_category_info = ProductCategory::select('faq_content', 'name')->where('slug', $filter_sub_sub_category)->first();
            $category_name = isset($product_category_info) ? $product_category_info->name : null;
            $faq_content = isset($product_category_info) ? $product_category_info->faq_content : null;
        } elseif ($request->scategory) {
            $product_category_info = ProductCategory::select('faq_content', 'name')->where('slug', $filter_sub_category)->first();
            $category_name = isset($product_category_info) ? $product_category_info->name : null;
            $faq_content = isset($product_category_info) ? $product_category_info->faq_content : null;
        } elseif ($request->category) {
            $product_category_info = ProductCategory::select('faq_content', 'name')->where('slug', $filter_category)->first();
            $category_name = isset($product_category_info) ? $product_category_info->name : null;
            $faq_content = isset($product_category_info) ? $product_category_info->faq_content : null;
        } else {
            $faq_content = null;
            $category_name = '';
        }
        // $to = count($details);
        $currentPage = $details->currentPage();
        $perPage = $details->perPage();

        $from = ($currentPage - 1) * $perPage + 1;
        $to = min($currentPage * $perPage, $total);
        $finalArray = [];

        if (isset($faq_content)) {
            $content_faq = str_replace(array("\r", "\n"), '', $faq_content);
            if (strpos($content_faq, ':') !== false) {
                // if(str_contains(':', $content_faq)){
                $asArr = explode('|', $content_faq);
                foreach ($asArr as $val) {
                    if (!empty($val)) {
                        $tmp_content = explode(':', $val);
                        $finalArray[] = ['question' => $tmp_content[0], 'answer' => $tmp_content[1]];
                    }
                }
            }
        }

        if ($category_name == null && (!empty($collection_name))) {
            $category_name = $collection_name;
        }
        $error = 0;
        $message = 'Success';
        $status = "success";
        $status_code = '200';
        $data_collection = array('side_filter_data' => $filter_data, 'name' => $category_name, 'meta_data' => $meta ?? '', 'products' => $tmp, 'faq' => $finalArray, 'total_count' => $total, 'from' => $from, 'to' => $to);
        return new Response(array('error' => $error, 'status_code' => $status_code, 'message' => $message, 'status' => $status, 'data' => $data_collection), $status_code);
    }

    public function getProductBySlug(Request $request)
    {

        $product_url = $request->product_url;
        $items = Product::where('product_url', $product_url)->withCount('views')->first();

        $category               = $items->productCategory;
        $salePrices             = getProductPrice($items);

        $pro                    = [];
        $pro['id']              = $items->id;
        $pro['product_name']    = $items->product_name;
        $pro['category_name']   = $category->name ?? '';
        $pro['category_slug']   = $category->slug ?? '';
        $pro['want_to_sell_yours_content'] = $category->description ?? '';
        $pro['parent_category_name']   = $category->parent->name ?? '';
        $pro['is_instrumental_category']   = $category->parent->is_instrumental_category ?? '';
        $pro['parent_category_slug']   = $category->parent->slug ?? '';
        $pro['brand_name']      = $items->productBrand->brand_name ?? '';
        $pro['brand_logo']      = $items->productBrand->brand_logo ?? '';
        $pro['hsn_code']        = $items->hsn_code;
        $pro['product_url']     = $items->product_url;
        $pro['sku']             = $items->sku;
        $pro['has_video_shopping'] = $items->has_video_shopping;
        $pro['stock_status']    = $items->stock_status;
        $pro['is_featured']     = $items->is_featured;
        $pro['is_best_selling'] = $items->is_best_selling;
        $pro['is_new']          = $items->is_new;
        $pro['sale_prices']     = $salePrices;
        $pro['mrp_price']       = $items->price;
        $pro['videolinks']      = $items->productVideoLinks;
        $pro['links']           = $items->productLinks;
        $pro['image']           = $items->base_image;
        $pro['max_quantity']    = $items->quantity;
        $pro['views_count']    = $items->views_count;
        $pro['ratings'] = $items->ratings->avg('star');

        $imagePath              = $items->base_image;

        if (!Storage::exists($imagePath)) {
            $path               = asset('userImage/no_Image.png');
        } else {
            $url                = Storage::url($imagePath);
            $path               = asset($url);
        }

        $pro['image']                   = $path;

        $pro['description']             = $items->description;
        $pro['technical_information']   = $items->technical_information;
        $pro['feature_information']     = $items->feature_information;
        $pro['specification']           = $items->specification;
        $pro['brochure_upload']         = $items->brochure_upload;
        $pro['reviews'] = $items->productReviews ?? [];
        // $pro['gallery']                 = $items->productImages;

        if (isset($items->productImages) && !empty($items->productImages)) {
            foreach ($items->productImages as $att) {

                $gallery_url            = Storage::url($att->gallery_path);
                $path                   = asset($gallery_url);

                $pro['gallery'][] = $path;
            }
        }


        $attributes = [];
        if (isset($items->productMappedAttributes) && !empty($items->productMappedAttributes)) {
            foreach ($items->productMappedAttributes as $attrItems) {
                $tmp = [];
                $tmp['id'] = $attrItems->attrInfo->id;
                $tmp['title'] = $attrItems->attrInfo->title;
                $tmp['slug'] = $attrItems->attrInfo->slug;
                $parent_sub = [];
                if (isset($attrItems->getFilterSpec) && !empty($attrItems->getFilterSpec)) {
                    foreach ($attrItems->getFilterSpec as $subitem) {
                        $sub_tmp = [];

                        $sub_tmp['id'] = $subitem->id;
                        $sub_tmp['title'] = $subitem->title;
                        $sub_tmp['value'] = $subitem->attribute_values;
                        $parent_sub[] = $sub_tmp;
                    }
                }
                $tmp['child'] = $parent_sub;

                $attributes[] = $tmp;
            }
        }
        $pro['attributes']              = $attributes;
        $related_arr                    = [];
        $cross_sale_arr                    = [];

        $productInfo            = Product::find(1340);

        if (isset($items->productRelated) && !empty($items->productRelated)) {
            foreach ($items->productRelated as $related) {

                $productInfo            = Product::find($related->to_product_id);

                $category               = $productInfo->productCategory;
                $salePrices1            = getProductPrice($productInfo);

                $tmp2                    = [];
                $tmp2['id']              = $productInfo->id;
                $tmp2['product_name']    = $productInfo->product_name;
                $tmp2['category_name']   = $category->name ?? '';
                $tmp2['brand_name']      = $productInfo->productBrand->brand_name ?? '';
                $tmp2['hsn_code']        = $productInfo->hsn_code;
                $tmp2['product_url']     = $productInfo->product_url;
                $tmp2['sku']             = $productInfo->sku;
                $tmp2['has_video_shopping'] = $productInfo->has_video_shopping;
                $tmp2['stock_status']    = $productInfo->stock_status;
                $tmp2['is_featured']     = $productInfo->is_featured;
                $tmp2['is_best_selling'] = $productInfo->is_best_selling;
                $tmp2['is_new']          = $productInfo->is_new;
                $tmp2['sale_prices']     = $salePrices1;
                $tmp2['mrp_price']       = $productInfo->price;
                $tmp2['image']           = $productInfo->base_image;

                $imagePath              = $productInfo->base_image;

                if (!Storage::exists($imagePath)) {
                    $path               = asset('assets/logo/no-img-1.png');
                } else {
                    $url                = Storage::url($imagePath);
                    $path               = asset($url);
                }

                $tmp2['image']           = $path;
                $related_arr[]          = $tmp2;
            }
        }

        if (isset($items->productCrossSale) && !empty($items->productCrossSale)) {
            foreach ($items->productCrossSale as $crossSale) {

                $productInfo            = Product::find($crossSale->to_product_id);

                $category               = $productInfo->productCategory;
                $salePrices1            = getProductPrice($productInfo);

                $tmp2                    = [];
                $tmp2['id']              = $productInfo->id;
                $tmp2['product_name']    = $productInfo->product_name;
                $tmp2['category_name']   = $category->name ?? '';
                $tmp2['brand_name']      = $productInfo->productBrand->brand_name ?? '';
                $tmp2['hsn_code']        = $productInfo->hsn_code;
                $tmp2['product_url']     = $productInfo->product_url;
                $tmp2['sku']             = $productInfo->sku;
                $tmp2['has_video_shopping'] = $productInfo->has_video_shopping;
                $tmp2['stock_status']    = $productInfo->stock_status;
                $tmp2['is_featured']     = $productInfo->is_featured;
                $tmp2['is_best_selling'] = $productInfo->is_best_selling;
                $tmp2['is_new']          = $productInfo->is_new;
                $tmp2['sale_prices']     = $salePrices1;
                $tmp2['mrp_price']       = $productInfo->price;
                $tmp2['image']           = $productInfo->base_image;

                $imagePath              = $productInfo->base_image;

                if (!Storage::exists($imagePath)) {
                    $path               = asset('assets/logo/no-img-1.png');
                } else {
                    $url                = Storage::url($imagePath);
                    $path               = asset($url);
                }

                $tmp2['image']           = $path;
                $cross_sale_arr[]          = $tmp2;
            }
        }
        $pro['product_extra_information'] = array(
            array('name' => 'description', 'data' => $items->specification, 'has_data' => isset($items->specification) && !empty($items->specification) ? true : false),
            array('name' => 'specification', 'data' => $attributes, 'has_data' => count($attributes) > 0 ? true : false),
            array('name' => 'media', 'data' => $items->productVideoLinks, 'has_data' => count($attributes) > 0 ? true : false),
        );

        $pro['related_products']    = $related_arr;
        $pro['combo_products']    = $cross_sale_arr;
        $pro['meta'] = $items->productMeta;
        $error = 0;
        $message = 'Success';
        $status = "success";
        $status_code = '200';
        return new Response(array('error' => $error, 'status_code' => $status_code, 'message' => $message, 'status' => $status, 'data' => $pro), $status_code);
    }

    public function globalSearch(Request $request)
    {
        /*  $search_type = $request->search_type;
        $category_id = $request->category_id;
        $query = $request->search_field;
        $category_ids = ProductCategory::select('id')->where('parent_id', $category_id)->get()->toArray();
        $searchData = [];
        $error = 1;
        if (!empty($query)) {

            $productInfo = Product::where(function ($qr) use ($query) {
                $qr->where('product_name', 'like', "%{$query}%")
                    ->orWhere('sku', 'like', "%{$query}%");
            })
            ->when( $category_id > 0,  function($q) use($category_ids) {
                return $q->whereIN('category_id', $category_ids);
            })
            ->where('status', 'published')->get();

            // if (count($productInfo) == 0) {
            //     $productInfo = Product::where(function ($qr) use ($query) {
            //         $qr->whereRaw("MATCH (mm_products.product_name) AGAINST ('" . $query . "' IN BOOLEAN MODE)")
            //             ->orWhere('sku', 'like', "%{$query}%");
            //     })
            //     ->where('status', 'published')->get();
            // }

            if (isset($productInfo) && !empty($productInfo) && count($productInfo) > 0) {
                $error = 0;
                foreach ($productInfo as $items) {

                    $category               = $items->productCategory;
                    $salePrices             = getProductPrice($items);

                    $pro                    = [];
                    $pro['has_data']        = 'yes';
                    $pro['id']              = $items->id;
                    $pro['product_name']    = $items->product_name;
                    $pro['category_id']    = $items->category_id;
                    $pro['category_name']   = $category->name ?? '';
                    $pro['category_slug']   = $category->slug ?? '';
                    $pro['parent_category_name']   = $category->parent->name ?? '';
                    $pro['parent_category_slug']   = $category->parent->slug ?? '';
                    $pro['brand_name']      = $items->productBrand->brand_name ?? '';
                    $pro['hsn_code']        = $items->hsn_code;
                    $pro['product_url']     = $items->product_url;
                    $pro['sku']             = $items->sku;
                    $pro['has_video_shopping'] = $items->has_video_shopping;
                    $pro['stock_status']    = $items->stock_status;
                    $pro['is_featured']     = $items->is_featured;
                    $pro['is_best_selling'] = $items->is_best_selling;
                    $pro['is_new']          = $items->is_new;
                    $pro['sale_prices']     = $salePrices;
                    $pro['mrp_price']       = $items->price;
                    $pro['videolinks']      = $items->productVideoLinks;
                    $pro['links']           = $items->productLinks;
                    $pro['image']           = $items->base_image;
                    $pro['max_quantity']    = $items->quantity;

                    $imagePath              = $items->base_image;

                    if (!Storage::exists($imagePath)) {
                        $path               = asset('assets/logo/no-img-1.png');
                    } else {
                        $url                = Storage::url($imagePath);
                        $path               = asset($url);
                    }

                    $pro['image']                   = $path;
                    $searchData[] = $pro;
                }

*/







        $search_type = $request->search_type;
        $category_id = $request->category_id;
        $query = $request->search_field;
        $page                   = $request->page ?? 0;
        // $filter_category        = $request->category;
        // $filter_sub_category    = $request->scategory;
        $filter_availability    = $request->availability;
        $filter_brand           = $request->brand;
        $filter_discount        = $request->discount;
        $filter_collection      = $request->collection;
        $filter_attribute       = $request->attributes_category;
        $sort                   = $request->sort;
        $minimum_price          = $request->minimum_price;
        $maximum_price          = $request->maximum_price;

        $filter_availability_array = [];
        $filter_attribute_array = [];
        $filter_brand_array = [];
        $filter_discount_array = [];
        $filter_collection_array = [];
        $filter_booking     = $request->booking;

        if (isset($filter_attribute) && !empty($filter_attribute)) {

            $filter_attribute_array = explode("-", $filter_attribute);
        }
        if (isset($filter_availability) && !empty($filter_availability)) {
            $filter_availability_array = explode("-", $filter_availability);
        }
        if (isset($filter_brand) && !empty($filter_brand)) {
            $filter_brand_array     = explode("_", $filter_brand);
        }

        if (isset($filter_discount) && !empty($filter_discount)) {
            $filter_discount_array     = explode("_", $filter_discount);
        }

        if (isset($filter_collection) && !empty($filter_collection)) {
            $filter_collection_array     = explode("_", $filter_collection);
        }

        // $category_info = ProductCategory::where('slug', $filter_category)->first();

        // $cat_id = $category_info->id ?? '';
        if ($category_id > 0) {
            $category_ids = ProductCategory::select('id')->where('parent_id', $category_id)->get()->toArray();
        } else {
            $category_ids = null;
        }
        // print_r($category_ids);

        // if (!empty($cat_id)) {
        //     $subCategories = ProductCategory::select(['id', 'name'])->where(['parent_id' => $cat_id, 'is_home_menu' => 'yes', 'status' => 'published'])
        //         ->orderBy('order_by', 'asc')
        //         ->get();
        //     $meta = CategoryMetaTags::where('category_id', $cat_id)->first();
        // }
        $productAttrNames = [];
        if (isset($filter_attribute_array) && !empty($filter_attribute_array)) {
            $productWithData = ProductWithAttributeSet::whereIn('id', $filter_attribute_array)->get();
            if (isset($productWithData) && !empty($productWithData)) {
                foreach ($productWithData as $attr) {
                    $productAttrNames[] = $attr->title;
                }
            }
        }

        $limit = 24;
        $skip = (isset($page) && !empty($page)) ? ($page * $limit) : 0;

        $from   = 1 + ($page * $limit);


        $take_limit = $limit + ($page * $limit);
        $total = Product::select('products.*')->where('products.status', 'published')
            // ->join('product_categories', function ($join) {
            //     $join->on('product_categories.id', '=', 'products.category_id');
            //     $join->orOn('product_categories.parent_id', '=', 'products.category_id');
            // })
            ->join('brands', 'brands.id', '=', 'products.brand_id')
            ->when($category_ids != '', function ($q) use ($category_ids) {
                $q->where(function ($q) use ($category_ids) {
                    return $q->whereIN('products.category_id', $category_ids);
                });
            })
            ->when($query != '', function ($q) use ($query) {
                $regexPattern = $query . '|' . substr($query, 0, -1); // seas|sea
                return $q->whereRaw("product_name REGEXP ?", [$regexPattern])->orderByRaw("product_name LIKE ? DESC", ["%{$query}%"]);
                // return $q->where('product_name', 'like', "%{$query}%");
            })
            ->when($filter_availability != '', function ($q) use ($filter_availability_array) {
                return $q->whereIn('products.stock_status', $filter_availability_array);
            })
            ->when($filter_brand != '', function ($q) use ($filter_brand_array) {
                return $q->whereIn('brands.slug', $filter_brand_array);
            })
            ->when($filter_booking == 'video_shopping', function ($q) {
                return $q->where('products.has_video_shopping', 'yes');
            })
            ->when($filter_discount != '', function ($q) use ($filter_discount_array) {
                $q->join('product_collections_products', 'product_collections_products.product_id', '=', 'products.id');
                $q->join('product_collections', 'product_collections.id', '=', 'product_collections_products.product_collection_id');
                return $q->whereIn('product_collections.slug', $filter_discount_array);
            })
            ->when($filter_collection != '', function ($q) use ($filter_collection_array) {
                $q->join('product_collections_products', 'product_collections_products.product_id', '=', 'products.id');
                $q->join('product_collections', 'product_collections.id', '=', 'product_collections_products.product_collection_id');
                return $q->whereIn('product_collections.slug', $filter_collection_array);
            })
            ->when($filter_attribute != '', function ($q) use ($productAttrNames) {
                $q->join('product_with_attribute_sets', 'product_with_attribute_sets.product_id', '=', 'products.id');
                return $q->whereIn('product_with_attribute_sets.title', $productAttrNames);
            })
            ->when((isset($minimum_price) && isset($maximum_price)), function ($q) use ($minimum_price, $maximum_price) {
                return $q->whereBetween('products.mrp', [$minimum_price, $maximum_price]);
            })
            ->when($sort == 'price_high_to_low', function ($q) {
                $q->orderBy('products.price', 'desc');
            })
            ->when($sort == 'price_low_to_high', function ($q) {
                $q->orderBy('products.price', 'asc');
            })
            ->when($sort == 'is_featured', function ($q) {
                $q->orderBy('products.is_featured', 'desc');
            })
            ->where('products.stock_status', '!=', 'out_of_stock')
            ->groupBy('products.id')
            ->get();
        $total = count($total);


        $details = Product::select('products.*')->where('products.status', 'published')
            // ->join('product_categories', function ($join) {
            //     $join->on('product_categories.id', '=', 'products.category_id');
            //     $join->orOn('product_categories.parent_id', '=', 'products.category_id');
            // })
            ->join('brands', 'brands.id', '=', 'products.brand_id')
            ->when(isset($category_ids), function ($q) use ($category_ids) {
                $q->where(function ($q) use ($category_ids) {
                    return $q->whereIN('products.category_id', $category_ids);
                });
            })
            ->when($query != '', function ($q) use ($query) {
                $regexPattern = $query . '|' . substr($query, 0, -1); // seas|sea
                return $q->whereRaw("product_name REGEXP ?", [$regexPattern])->orderByRaw("product_name LIKE ? DESC", ["%{$query}%"]);
                // return $q->where('product_name', 'like', "%{$query}%");
            })
            ->when($filter_availability != '', function ($q) use ($filter_availability_array) {
                return $q->whereIn('products.stock_status', $filter_availability_array);
            })
            ->when($filter_brand != '', function ($q) use ($filter_brand_array) {
                return $q->whereIn('brands.slug', $filter_brand_array);
            })
            ->when($filter_booking == 'video_shopping', function ($q) {
                return $q->where('products.has_video_shopping', 'yes');
            })
            ->when($filter_discount != '', function ($q) use ($filter_discount_array) {
                $q->join('product_collections_products', 'product_collections_products.product_id', '=', 'products.id');
                $q->join('product_collections', 'product_collections.id', '=', 'product_collections_products.product_collection_id');
                return $q->whereIn('product_collections.slug', $filter_discount_array);
            })
            ->when($filter_collection != '', function ($q) use ($filter_collection_array) {
                $q->join('product_collections_products', 'product_collections_products.product_id', '=', 'products.id');
                $q->join('product_collections', 'product_collections.id', '=', 'product_collections_products.product_collection_id');
                return $q->whereIn('product_collections.slug', $filter_collection_array);
            })
            ->when($filter_attribute != '', function ($q) use ($productAttrNames) {
                $q->join('product_with_attribute_sets', 'product_with_attribute_sets.product_id', '=', 'products.id');
                return $q->whereIn('product_with_attribute_sets.title', $productAttrNames);
            })
            ->when((isset($minimum_price) && isset($maximum_price)), function ($q) use ($minimum_price, $maximum_price) {
                return $q->whereBetween('products.sale_price', [$minimum_price, $maximum_price]);
            })
            ->when($sort == 'price_high_to_low', function ($q) {
                $q->orderBy('products.mrp', 'desc');
            })
            ->when($sort == 'price_low_to_high', function ($q) {
                $q->orderBy('products.mrp', 'asc');
            })
            ->when($sort == 'is_featured', function ($q) {
                $q->orderBy('products.is_featured', 'desc');
            })
            ->where('products.stock_status', '!=', 'out_of_stock')
            ->groupBy('products.id')
            ->skip(0)->take($take_limit)
            ->get();

        if (isset($details) && !empty($details)) {
            $tmp = [];
            // $meta            = (isset($details[0])) ? $details[0]->productCategory->meta : [];
            foreach ($details as $items) {

                $category               = $items->productCategory;

                $salePrices             = getProductPrice($items);

                $pro                    = [];

                $pro['id']              = $items->id;
                $pro['product_name']    = $items->product_name;
                $pro['category_name']   = $category->name ?? '';
                $pro['brand_name']      = $items->productBrand->brand_name ?? '';
                $pro['hsn_code']        = $items->hsn_code;
                $pro['product_url']     = $items->product_url;
                $pro['sku']             = $items->sku;
                $pro['has_video_shopping'] = $items->has_video_shopping;
                $pro['stock_status']    = $items->stock_status;
                $pro['is_featured']     = $items->is_featured;
                $pro['is_best_selling'] = $items->is_best_selling;
                $pro['is_new']          = $items->is_new;
                $pro['sale_prices']     = $salePrices;
                $pro['mrp_price']       = $items->mrp;
                $pro['image']           = $items->base_image;
                $pro['max_quantity']    = $items->quantity;
                $imagePath              = $items->base_image;

                if (!Storage::exists($imagePath)) {
                    $path               = asset('assets/logo/product-noimg.png');
                } else {
                    $url                = Storage::url($imagePath);
                    $path               = asset($url);
                }

                $pro['image']           = $path;

                $tmp[] = $pro;
            }
        }

        // if ($total < $limit) {
        //     $to = $total;
        // }
        $to = count($details);
        $error = 0;
        $message = 'Success';
        $status = "success";
        $status_code = '200';
        $data_collection = array('meta_data' => $meta ?? '', 'products' => $tmp, 'total_count' => $total, 'from' => ($total == 0 ? '0' : '1'), 'to' => $to);
        return new Response(array('error' => $error, 'status_code' => $status_code, 'message' => $message, 'status' => $status, 'data' => $data_collection), $status_code);









        // } else {
        //     return new Response(array('error' => 0, 'status_code' => '200', 'message' => 'No data found', 'status' => 'failed', 'data' => []), 200);
        // }


        // return new Response(array('error' => $error, 'status_code' => '200', 'message' => 'Data loaded successfully', 'status' => $error, 'data' => $searchData), 200);
    }

    public function getOtherCategories(Request $request)
    {

        $category       = $request->category;

        $otherCategory   = ProductCategory::select('id', 'name', 'slug')
            ->when($category != '', function ($q) use ($category) {
                $q->where('slug', '!=', $category);
            })
            ->where(['status' => 'published', 'parent_id' => 0])
            ->orderBy('order_by', 'asc')
            ->get();
        $data = [];
        if (isset($otherCategory) && !empty($otherCategory)) {
            foreach ($otherCategory as $item) {

                $tmp = [];
                $tmp['id'] = $item->id;
                $tmp['name'] = $item->name;
                $tmp['slug'] = $item->slug;
                $tmp['description'] = $item->description;

                $imagePath              = $item->image;

                if (!Storage::exists($imagePath)) {
                    $path               = asset('assets/logo/no-img-1.png');
                } else {
                    $url                = Storage::url($imagePath);
                    $path               = asset($url);
                }

                $tmp['image'] = $path;

                $data[] = $tmp;
            }
        }
        return new Response(array('error' => 0, 'status_code' => '200', 'message' => 'Data loaded successfully', 'status' => 'success', 'data' => $data), 200);
    }

    public function getDynamicFilterCategory(Request $request)
    {
        // dd( $request->all() );
        $category_slug = $request->category_slug;
        // $category_slug = 'keyboard-keyboard';
        $productCategory = ProductCategory::where('slug', $category_slug)->first();
        if (isset($productCategory) && !empty($productCategory)) {
            $cat_id = $productCategory->id;
            $brands = Brands::select('id', 'brand_name', 'slug')
                ->where('brand_name', '!=', '')
                ->where('status', 'published')
                ->get();
            // $brands = Product::select('brands.id', 'brands.brand_name', 'brands.slug')
            //     ->join('brands', 'brands.id', '=', 'products.brand_id')
            //     ->join('product_categories', function ($join) {
            //         $join->on('product_categories.id', '=', 'products.category_id');
            //         $join->orOn('product_categories.parent_id', '=', 'products.category_id');
            //     })
            //     ->where(function ($query) use ($cat_id) {
            //         return $query->where('product_categories.id', $cat_id)->orWhere('product_categories.parent_id', $cat_id);
            //     })
            //     ->where('products.stock_status', 'in_stock')
            //     ->where('brands.brand_name', '!=', '')
            //     ->where('products.status', 'published')->groupBy('products.brand_id')
            //     ->get();

            $price_range = Product::select('mrp')
                ->selectRaw(" MIN(mrp) as minimum_price, MAX(mrp) as maximum_price")
                ->join('product_categories', function ($join) {
                    $join->on('product_categories.id', '=', 'products.category_id');
                    $join->orOn('product_categories.parent_id', '=', 'products.category_id');
                })
                ->where(function ($query) use ($cat_id) {
                    return $query->where('product_categories.id', $cat_id)->orWhere('product_categories.parent_id', $cat_id);
                })
                ->where('products.stock_status', 'in_stock')
                ->where('products.status', 'published')->groupBy('products.brand_id')
                ->get();


            $whereIn = [];
            $whereIn[] = $productCategory->id;
            $tmp_sub_category = [];
            if (isset($productCategory->childCategory) && !empty($productCategory->childCategory)) {
                foreach ($productCategory->childCategory  as $items) {
                    $tmp_sub_category[] = [
                        'id' => $items->id,
                        'name' => $items->name,
                        'category_slug' => strtolower(str_replace(' ', '-', $items->name)),
                        'slug' => $items->slug,
                    ];
                    $whereIn[] = $items->id;
                }
            }

            $data = [];
            $attributes = [];
            $topLevelData = Product::select('product_attribute_sets.id', 'product_attribute_sets.title', 'product_attribute_sets.slug')
                ->whereIn('category_id', $whereIn)
                ->join('product_map_attributes', 'product_map_attributes.product_id', '=', 'products.id')
                ->join('product_attribute_sets', 'product_attribute_sets.id', '=', 'product_map_attributes.attribute_id')
                ->where('products.stock_status', 'in_stock')
                ->where('product_attribute_sets.is_searchable', '1')
                ->groupBy('title')->get();

            if (isset($topLevelData) && !empty($topLevelData)) {
                foreach ($topLevelData as $vals) {
                    $tmp = [];
                    $tmp['id'] = $vals->id;
                    $tmp['title'] = $vals->title;
                    $tmp['slug'] = $vals->slug;
                    $child = [];
                    $secondLevelData = Product::select('product_with_attribute_sets.id', 'product_with_attribute_sets.title', 'product_with_attribute_sets.attribute_values')
                        ->join('product_map_attributes', 'product_map_attributes.product_id', '=', 'products.id')
                        ->join('product_with_attribute_sets', 'product_with_attribute_sets.product_attribute_set_id', '=', 'product_map_attributes.id')
                        ->where('products.stock_status', 'in_stock')
                        ->whereIn('category_id', $whereIn)
                        ->where('product_map_attributes.attribute_id', $vals->id)
                        ->groupBy('title')->get();
                    if (isset($secondLevelData) && !empty($secondLevelData)) {

                        foreach ($secondLevelData as $sec) {
                            $fValues = [];
                            $fValues['id'] = $sec->id;
                            $fValues['title'] = $sec->title;
                            // $fValues['attribute_values'] = $sec->attribute_values;

                            $filterDatas = Product::select('product_with_attribute_sets.id', 'product_with_attribute_sets.title', 'product_with_attribute_sets.attribute_values')
                                ->join('product_map_attributes', 'product_map_attributes.product_id', '=', 'products.id')
                                ->join('product_with_attribute_sets', 'product_with_attribute_sets.product_attribute_set_id', '=', 'product_map_attributes.id')
                                ->where('products.stock_status', 'in_stock')
                                ->whereIn('category_id', $whereIn)
                                ->where('product_map_attributes.attribute_id', $vals->id)
                                ->where('product_with_attribute_sets.title', $sec->title)
                                ->groupBy('product_with_attribute_sets.title')
                                ->get();
                            if (isset($filterDatas) && !empty($filterDatas)) {

                                foreach ($filterDatas as $filvalues) {
                                    $childValues = [];
                                    $childValues['id']  = $filvalues->id;
                                    $childValues['attribute_name']  = $filvalues->title;
                                    $childValues['attribute_values']  = $filvalues->attribute_values;

                                    $fValues['child'][] = $childValues;
                                }
                            }

                            $tmp['child'][] = $fValues;
                        }
                    }

                    $attributes[] = $tmp;
                }
            }
        } else {
            $brands = Product::select('brands.id', 'brands.brand_name', 'brands.slug')
                ->join('brands', 'brands.id', '=', 'products.brand_id')
                ->join('product_categories', function ($join) {
                    $join->on('product_categories.id', '=', 'products.category_id');
                    $join->orOn('product_categories.parent_id', '=', 'products.category_id');
                })
                ->where('products.stock_status', 'in_stock')
                ->where('products.status', 'published')->groupBy('products.brand_id')
                ->get();
        }

        $priceFilter = [
            [
                'id' => 1,
                'name' => '500 - 1000',
                'min' => 500,
                'max' => 1000,
            ],
            [
                'id' => 2,
                'name' => '1000 - 5000',
                'min' => 1000,
                'max' => 5000,
            ],
            [
                'id' => 3,
                'name' => '5000 - 25000',
                'min' => 5000,
                'max' => 25000,
            ],
            [
                'id' => 4,
                'name' => '25000 - 1000000',
                'min' => 25000,
                'max' => 1000000,
            ],
            [
                'id' => 5,
                'name' => '1000000 - above',
                'min' => 1000000,
                'max' => 10000000,
            ],
        ];

        $product_availability = array(
            'in_stock' => 'In Stock',
            'coming_soon' => 'Coming Soon',
        );


        $data = array('sub_categories' => $tmp_sub_category ?? [], 'price-filter' => $priceFilter ?? [], 'product_availability' => $product_availability ?? [], 'attributes' => $attributes ?? [], 'brands' => $brands ?? []);
        return new Response(array('error' => 0, 'status_code' => '200', 'message' => 'Data loaded successfully', 'status' => 'success', 'data' => $data), 200);
    }

    /**
     * Method getAccessoriesProductsByCategory
     *
     * Accessories list - as all the slug contains there parent category slug checked with the slug and created collection
     *
     * @param Request $request [explicite description]
     *
     * @return void
     */
    public function getAccessoriesProductsByCategory(Request $request)
    {
        $category_slug = $request->category_slug;
        $finalArray = [];

        $accessories_category_list =  ProductCategory::where('slug', 'LIKE', '%accessories%')->pluck('id')->toArray();
        $accessories_category = ProductCategory::whereIn('id', $accessories_category_list)->where('slug', 'LIKE', '%' . $category_slug . '%')->first();

        if ($accessories_category) {
            $accessories_category_products = Product::whereIn('category_id', $accessories_category)->orderBy('id', 'desc')->limit(10)->get();
        } else {

            $category_ids = ProductCategory::where('slug', 'other-accessories-accessories')->first();
            if ($category_ids) {
                $category_id = $category_ids->id;
            }
            $accessories_category_products = Product::where('category_id', $category_id)->orderBy('id', 'desc')->limit(10)->get();
        }

        $tmp['accessories'] = $this->getProductsData($accessories_category_products, $accessories_category);

        $recently_uploaded_products_category = ProductCategory::select('id', 'name', 'description', 'category_banner', 'slug')->where('slug', $category_slug)->first();
        if ($recently_uploaded_products_category) {
            $recently_uploaded_product_category_id = $recently_uploaded_products_category->id;

            $tmp['category_details'] = $recently_uploaded_products_category;
            $tmp['sub_categories'] = ProductCategory::where([['parent_id', $recently_uploaded_product_category_id], ['status', 'published']])->get();

            $tmp['random_color_section'] = $this->getProductCollectionByColorCode($recently_uploaded_product_category_id);

            $check_is_parent = $recently_uploaded_products_category->parent_id;


            if ($check_is_parent != 0) {
                $recently_uploaded_products = Product::where(['category_id' =>  $recently_uploaded_product_category_id, 'status' => 'published', 'stock_status' => 'in_stock'])->orderBy('id', 'desc')->limit(10)->get();
                $featured_products = Product::where(['category_id' =>  $recently_uploaded_product_category_id, 'is_featured' => 1, 'status' => 'published', 'stock_status' => 'in_stock'])->orderBy('id', 'asc')->limit(10)->get();
            } else {
                $category_list =  ProductCategory::where('parent_id', $recently_uploaded_product_category_id)->pluck('id')->toArray();

                $recently_uploaded_products = Product::whereIn('category_id', $category_list)->where(['status' => 'published', 'stock_status' => 'in_stock'])->orderBy('id', 'desc')->limit(10)->get();
                $featured_products = Product::whereIn('category_id',  $category_list)->where(['is_featured' => 1, 'status' => 'published', 'stock_status' => 'in_stock'])->orderBy('id', 'asc')->limit(10)->get();
            }

            $tmp['recently_uploaded_products'] = $this->getProductsData($recently_uploaded_products, $recently_uploaded_products_category);
            $tmp['featured_products'] = $this->getProductsData($featured_products, $recently_uploaded_products_category);


            $faq_content = ProductCategory::select('faq_content')->where('slug', $category_slug)->first();


            if (isset($faq_content)) {
                $content_faq = str_replace(array("\r", "\n"), '', $faq_content->faq_content);
                if (strpos($content_faq, ':') !== false) {
                    // if(str_contains(':', $content_faq)){
                    $asArr = explode('|', $content_faq);
                    foreach ($asArr as $val) {
                        if (!empty($val)) {
                            $tmp_content = explode(':', $val);
                            $finalArray[] = ['question' => $tmp_content[0], 'answer' => $tmp_content[1]];
                        }
                    }
                }
            }
        } else {
            $tmp['random_color_section'] = [];
            $tmp['recently_uploaded_products'] = [];
            $tmp['featured_products'] = [];
            $tmp['category_details'] = [];
        }

        $tmp['faq'] = $finalArray;

        return new Response(array('error' => 0, 'status_code' => '200', 'message' => 'Data loaded successfully', 'status' => 'success', 'data' => $tmp), 200);
    }

    /**
     * Method productCollectionByColorCode
     *
     *
     * @return void
     */
    public function getProductCollectionByColorCode($category_id)
    {
        $banner_image = '';
        $colors = ['#00968A', '#231F58', '#E34061', '#F28BA9', '#512A96', '#6B1579'];
        $details = ProductCollection::where(['connected_with_category' => 1, 'status' => 'published', 'category_id' => $category_id])
            ->orderBy('order_by', 'desc')->get();
        $data = [];
        foreach ($details as $detail) {
            if (isset($detail->banner_image) && (!empty($detail->banner_image))) {
                $bannerImagePath        = 'productCollection/' . $detail->id . '/' . $detail->banner_image;
                $url                    = Storage::url($bannerImagePath);
                $banner_image = asset($url);
            }

            $data[] = [
                'title' => $detail->collection_name,
                'price_range_text' => $detail->tag_line,
                'random_color' => $colors[array_rand($colors)],
                'banner' => $banner_image
            ];
        }
        return $data;
    }


    /**
     * Method getProductsData
     *
     * @param $accessories_category_products $accessories_category_products [explicite description]
     * @param $accessories_category $accessories_category [explicite description]
     *
     * @return void
     */
    public function getProductsData($accessories_category_products, $accessories_category)
    {
        $tmp = [];
        foreach ($accessories_category_products as $product) {

            $salePrices             = getProductPrice($product);

            $pro                    = [];
            $pro['id']              = $product->id;
            $pro['product_name']    = $product->product_name;
            $pro['category_name']   = $accessories_category->name ?? '';
            $pro['hsn_code']        = $product->hsn_code;
            $pro['product_url']     = $product->product_url;
            $pro['sku']             = $product->sku;
            $pro['has_video_shopping'] = $product->has_video_shopping;
            $pro['stock_status']    = $product->stock_status;
            $pro['is_featured']     = $product->is_featured;
            $pro['is_best_selling'] = $product->is_best_selling;
            $pro['is_new']          = $product->is_new;
            $pro['sale_prices']     = $salePrices;
            $pro['mrp_price']       = $product->price;
            $pro['image']           = $product->base_image;
            $pro['max_quantity']    = $product->quantity;
            $imagePath              = $product->base_image;

            if (!Storage::exists($imagePath)) {
                $path               = asset('assets/logo/no-img-1.png');
            } else {
                $url                = Storage::url($imagePath);
                $path               = asset($url);
            }

            $pro['image']           = $path;

            $tmp[]      = $pro;
        }
        return $tmp;
    }

    public function getCategoriesSectionHome()
    {
        $product_categories = ProductCategory::select('name', 'image_sm')->where(['status' => 'published', 'parent_id' => 0])->limit(9)->orderBy('id', 'desc')->get();
        $data = [];
        foreach ($product_categories as $product_category) {
            $imagePath = $product_category->image_sm;
            if (!Storage::exists($imagePath)) {
                $path               = asset('assets/logo/no-img-1.png');
            } else {
                $url                = Storage::url($imagePath);
                $path               = asset($url);
            }
            $data[] = ['name' => $product_category->name, 'image' => $path];
        }

        return new Response(array('error' => 0, 'status_code' => '200', 'message' => 'Data loaded successfully', 'status' => 'success', 'data' => $data), 200);
    }
}
