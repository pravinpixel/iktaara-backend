<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\BannerResource;
use App\Http\Resources\BrandResource;
use App\Http\Resources\DiscountCollectionResource;
use App\Http\Resources\HistoryVideoResource;
use App\Http\Resources\ProductCollectionResource;
use App\Http\Resources\TestimonialResource;
use App\Models\Banner;
use App\Models\GlobalSettings;
use App\Models\Master\BrandCategory;
use App\Models\TopbarContent;
use App\Models\Master\Brands;
use App\Models\Master\City;
use App\Models\Master\DynamicBrandCategory;
use App\Models\Master\Pincode;
use App\Models\Master\State;
use App\Models\MetaContent;
use App\Models\Offers\Coupons;
use App\Models\Product\Product;
use App\Models\Product\ProductCategory;
use App\Models\Product\ProductCollection;
use App\Models\Product\ProductDiscount;
use App\Models\RecentView;
use App\Models\Testimonials;
use App\Models\WalkThrough;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class CommonController extends Controller
{

    public function getAllTestimonials()
    {
        $data = TestimonialResource::collection(Testimonials::select('id', 'title', 'image', 'short_description', 'long_description')->where(['status' => 'published'])->orderBy('order_by', 'asc')->get());
        return new Response(array('error' => 0, 'status_code' => 200, 'message' => 'Data loaded successfully', 'status' => 'success', 'data' => $data), 200);
    }

    public function getAllHistoryVideo()
    {
        $data = HistoryVideoResource::collection(WalkThrough::select('id', 'title', 'video_url', 'file_path', 'description')->where(['status' => 'published'])->orderBy('order_by', 'asc')->get());
        return new Response(array('error' => 0, 'status_code' => 200, 'message' => 'Data loaded successfully', 'status' => 'success', 'data' => $data), 200);
    }

    public function getAllBanners()
    {
        $data = BannerResource::collection(Banner::select('id', 'title', 'description', 'banner_image', 'tag_line', 'order_by')->where(['status' => 'published'])->orderBy('order_by', 'asc')->get());
        return new Response(array('error' => 0, 'status_code' => 200, 'message' => 'Data loaded successfully', 'status' => 'success', 'data' => $data), 200);
    }

    public function getAllBrands()
    {
        $data = Brands::select('id', 'brand_name', 'brand_banner', 'brand_logo', 'short_description', 'notes', 'slug')->where(['status' => 'published'])->orderBy('order_by', 'asc')->get()->toArray();
        return new Response(array('error' => 0, 'status_code' => 200, 'message' => 'Data loaded successfully', 'status' => 'success', 'data' => $data), 200);
    }

    public function getHomePageBrands()
    {
        $data['brands'] = Brands::select('id', 'brand_name', 'brand_banner', 'brand_logo', 'short_description', 'notes', 'slug')->withCount('products')->where(['status' => 'published'])->whereNotNull('brand_logo')->orderBy('products_count', 'desc')->get()->toArray();
        $data['title'] = 'Brands you love';
        $data['description'] = 'Personalised collections from your favorite brands';
        return new Response(array('error' => 0, 'status_code' => 200, 'message' => 'Data loaded successfully', 'status' => 'success', 'data' => $data), 200);
    }

    public function getBrandByAlphabets()
    {

        $alphas = range('A', 'Z');

        $checkArray = [];
        if (isset($alphas) && !empty($alphas)) {
            foreach ($alphas as $items) {

                $data = Brands::where(DB::raw('SUBSTR(brand_name, 1, 1)'), strtolower($items))
                    ->orderBy('order_by', 'asc')
                    ->get();
                $childTmp = [];
                if (isset($data) && !empty($data)) {
                    foreach ($data as $daitem) {
                        $tmp1                    = [];
                        // $brandLogoPath           = 'brands/' . $daitem->id . '/default/' . $daitem->brand_logo;

                        // if ($daitem->brand_logo === null) {
                        //     $path                = asset('assets/logo/no-img-1.png');
                        // } else {
                        //     $url                 = Storage::url($brandLogoPath);
                        //     $path                = asset($url);
                        // }

                        $tmp1['id']            = $daitem->id;
                        $tmp1['title']         = $daitem->brand_name;
                        $tmp1['slug']          = $daitem->slug;
                        $tmp1['image']         = $daitem->brand_logo;
                        $tmp1['brand_banner']  = $daitem->brand_banner;
                        $tmp1['description']   = $daitem->short_description;
                        $tmp1['notes']         = $daitem->notes;

                        $childTmp[]     = $tmp1;
                    }
                }
                $tmp[$items]  = $childTmp;
                $checkArray   = $tmp;
            }
        }
        return new Response(array('error' => 0, 'status_code' => 200, 'message' => 'Data loaded successfully', 'status' => 'success', 'data' => $checkArray), 200);
    }

    public function getDiscountCollections()
    {

        $details        = ProductCollection::where(['show_home_page' => 'yes', 'status' => 'published'])
            ->orderBy('order_by', 'asc')->limit(12)->get();

        $collection     = [];

        if (isset($details) && !empty($details)) {
            foreach ($details as $item) {
                $tmp                    = [];
                $tmp['id']              = $item->id;
                $tmp['collection_name'] = $item->collection_name;
                $tmp['slug']            = $item->slug;
                $tmp['tag_line']        = $item->tag_line;
                $tmp['order_by']        = $item->order_by;

                if (isset($item->collectionProducts) && !empty($item->collectionProducts)) {
                    $i = 0;
                    foreach ($item->collectionProducts as $proItem) {
                        $pro                    = [];
                        // if ($i == 4) {
                        //     break;
                        // }
                        $productInfo            = Product::find($proItem->product_id);
                        if ($productInfo && ($productInfo->status == "published")) {
                            $salePrices             = getProductPrice($productInfo);

                            $pro['id']              = $productInfo->id;
                            $pro['product_name']    = $productInfo->product_name;
                            $pro['hsn_code']        = $productInfo->hsn_code;
                            $pro['product_url']     = $productInfo->product_url;
                            $pro['sku']             = $productInfo->sku;
                            $pro['has_video_shopping'] = $productInfo->has_video_shopping;
                            $pro['stock_status']    = $productInfo->stock_status;
                            $pro['is_featured']     = $productInfo->is_featured;
                            $pro['is_best_selling'] = $productInfo->is_best_selling;
                            $pro['is_new']          = $productInfo->is_new;
                            $pro['sale_prices']     = $salePrices;
                            $pro['mrp_price']       = $productInfo->price;
                            $pro['image']           = $productInfo->base_image;
                            $pro['max_quantity']           = $productInfo->quantity;
                            $pro['category']        = $productInfo->ProductCategory->name ?? null;
                            $pro['category_slug']   = $productInfo->ProductCategory->slug ?? null;

                            $imagePath              = $productInfo->base_image;

                            if (!Storage::exists($imagePath)) {
                                $path               = asset('assets/logo/no-img-1.png');
                            } else {
                                $url                = Storage::url($imagePath);
                                $path               = asset($url);
                            }

                            $pro['image']           = $path;

                            $tmp['products'][]      = $pro;

                            $i++;
                        }
                    }
                }

                $collection[] = $tmp;
            }
        }
        return new Response(array('error' => 0, 'status_code' => 200, 'message' => 'Data loaded successfully', 'status' => 'success', 'data' => $collection), 200);
    }

    public function setRecentView(Request $request)
    {
        $customer_id = $request->customer_id;
        $guest_token = $request->guest_token;

        $product_url = $request->product_url;
        $product_info = Product::where('product_url', $product_url)->first();
        if (!isset($product_info)) {
            return new Response(array('error' => 1, 'status_code' => 400, 'message' => 'Product not found', 'status' => 'failure', 'data' => []), 400);
        }
        $ins['product_id'] = $product_info->id;
        if (isset($guest_token)) {
            $ins['guest_token'] = $guest_token;
            RecentView::where('guest_token', $guest_token)->where('product_id', $product_info->id)->delete();
        } else if (isset($customer_id)) {
            $ins['customer_id'] = $customer_id;
            RecentView::where('customer_id', $request->customer_id)->where('product_id', $product_info->id)->delete();
        }

        RecentView::create($ins);

        return new Response(array('error' => 0, 'status_code' => 200, 'message' => 'Review set successfully', 'status' => 'success', 'data' => []), 200);
    }

    public function getSates()
    {
        $data = State::select('state_name', 'id', 'state_code')->where('status', 1)->get();
        return new Response(array('error' => 0, 'status_code' => 200, 'message' => 'Data loaded successfully', 'status' => 'success', 'data' => $data), 200);
    }
    public function getCities()
    {
        $data =  City::where('status', 1)->get();
        $city = [];
        foreach ($data as $key => $val) {
            $temp = [];
            $temp['id'] = $val['id'];
            $temp['name'] = $val['city'];
            $temp['state']['id']         = $val->state->id ?? '';
            $temp['state']['state_name'] = $val->state->state_name ?? '';
            $temp['state']['state_code'] = $val->state->state_code ?? '';
            $city[] = $temp;
        }
        return new Response(array('error' => 0, 'status_code' => 200, 'message' => 'Data loaded successfully', 'status' => 'success', 'data' => $city), 200);
    }
    public function getPincode()
    {
        $data = Pincode::select('id', 'pincode', 'description')->where('status', 1)->get();
        return new Response(array('error' => 0, 'status_code' => 200, 'message' => 'Data loaded successfully', 'status' => 'success', 'data' => $data), 200);
    }


    public function getMetaInfo(Request $request)
    {
        $page = $request->page;
        $data = MetaContent::where('page_name', $page)->first();

        return new Response(array('error' => 0, 'status_code' => 200, 'message' => 'Data loaded successfully', 'status' => 'success', 'data' => $data), 200);
    }

    public function getAllHomeDetails()
    {

        $details = ProductCollection::where(['show_home_page' => 'yes', 'status' => 'published', 'can_map_discount' => 'no'])
            ->orderBy('order_by', 'asc')->get();

        foreach ($details as $key => $val) {
            if (!empty($val['banner_image'])) {
                $bannerImagePath        = 'productCollection/' . $val->id . '/' . $val->banner_image;
                $url                    = Storage::url($bannerImagePath);
                $val['banner_image']           = asset($url);
            } else {
                $val['banner_image']           = asset('assets/media/product_collection/product_collection.jpg');
            }
        }
        $response['collection'] = ProductCollectionResource::collection($details);
        $response['testimonials'] =  TestimonialResource::collection(Testimonials::select('id', 'title', 'image', 'short_description', 'long_description')->where(['status' => 'published'])->orderBy('order_by', 'asc')->get());
        $response['video'] = HistoryVideoResource::collection(WalkThrough::select('id', 'title', 'video_url', 'file_path', 'description')->where(['status' => 'published'])->orderBy('order_by', 'asc')->get());
        $response['banner'] = BannerResource::collection(Banner::select('id', 'title', 'description', 'banner_image', 'links', 'mobile_banner', 'tag_line', 'order_by')->where(['status' => 'published'])->orderBy('order_by', 'asc')->get());

        return new Response(array('error' => 0, 'status_code' => 200, 'message' => 'Data loaded successfully', 'status' => 'success', 'data' => $response), 200);
    }

    public function getBrandInfo(Request $request)
    {

        $slug = $request->slug;
        $brand_info = Brands::where('slug', $slug)->first();

        if (isset($brand_info->brand_banner) && !empty($brand_info->brand_banner)) {

            $bannerImagePath        = 'brands/' . $brand_info->id . '/banner/' . $brand_info->brand_banner;
            $url                    = Storage::url($bannerImagePath);
            $banner_path            = asset($url);
        } else {
            $banner_path = asset('assets/logo/no_img_category_banner.jpg');
        }

        if (isset($brand_info->brand_logo) && !empty($brand_info->brand_logo)) {

            $logoImagePath          = 'brands/' . $brand_info->id . '/default/' . $brand_info->brand_logo;
            $url                    = Storage::url($logoImagePath);
            $logo_path              = asset($url);
        } else {
            $logo_path = null;
        }

        $response['brand_info'] = $brand_info;
        $parent['id'] = $brand_info->id;
        $parent['name'] = $brand_info->name;
        $parent['slug'] = $brand_info->slug;
        $parent['logo'] = $logo_path;
        $parent['banner'] = $banner_path;
        if ($brand_info->category) {
            foreach ($brand_info->category as $items) {
                $tmp = [];
                $cat_id = $items->id;
                $parent_cat_id = $items->parent_id;

                $products  = Product::where('brand_id', $brand_info->id)->where(function ($query) use ($cat_id, $parent_cat_id) {
                    $query->where('category_id', $cat_id);
                    if ($parent_cat_id) {
                        $query->orWhere('category_id', $parent_cat_id);
                    }
                })->first();
                $tmp['id'] = $items->id;
                $tmp['name'] = $items->name;
                $tmp['slug'] = $items->slug;
                /**** check product has brand */

                if ($items->image) {
                    $catImagePath = 'productCategory/' . $items->id . '/default/' . $items->image;
                    $url = Storage::url($catImagePath);
                    $path = asset($url);
                } else {

                    $path = asset('assets/logo/no_img_category_lg.jpg');
                }
                $tmp['image'] = $path;
                /**
                 * small images
                 */
                if ($items->image_sm) {
                    $catImagePath1 = 'productCategory/' . $items->id . '/small/' . $items->image_sm;
                    $url1 = Storage::url($catImagePath1);
                    $path1 = asset($url1);
                } else {
                    $path1 = asset('assets/logo/no_img_category_sm.jpg');
                }
                $tmp['image_sm'] = $path1;
                /**
                 * medium images
                 */
                if ($items->image_md) {
                    $catImagePath2 = 'productCategory/' . $items->id . '/medium/' . $items->image_md;
                    $url2 = Storage::url($catImagePath2);
                    $path2 = asset($url2);
                } else {
                    $path2 = asset('assets/logo/no_img_category_md.jpg');
                }
                $tmp['image_md'] = $path2;

                if ($products) {


                    /**
                     * get sub category
                     */
                    $sub_category_info =  ProductCategory::select('product_categories.*')->join('products', 'products.category_id', '=', 'product_categories.id')
                        ->join('brands', 'brands.id', '=', 'products.brand_id')
                        ->where('product_categories.parent_id', $items->id)
                        ->groupBy('product_categories.id')->get();
                    $sub_category = [];
                    if (isset($sub_category_info) && !empty($sub_category_info)) {
                        foreach ($sub_category_info as $catitem) {
                            $tmp1 = [];
                            $tmp1['id'] = $catitem->id;
                            $tmp1['name'] = $catitem->name;
                            $tmp1['slug'] = $catitem->slug;
                            if ($catitem->image) {
                                $catImagePath = 'productCategory/' . $catitem->id . '/default/' . $catitem->image;
                                $url = Storage::url($catImagePath);
                                $path1 = asset($url);
                            } else {
                                $path1 = asset('assets/logo/no_img_category_lg.jpg');
                            }
                            $tmp1['image'] = $path1;

                            /**
                             * small images
                             */
                            if ($catitem->image_sm) {
                                $catImagePath1 = 'productCategory/' . $catitem->id . '/small/' . $catitem->image_sm;
                                $url1 = Storage::url($catImagePath1);
                                $path1 = asset($url1);
                            } else {
                                $path1 = asset('assets/logo/no_img_category_sm.jpg');
                            }
                            $tmp1['image_sm'] = $path1;
                            /**
                             * medium images
                             */
                            if ($catitem->image_md) {
                                $catImagePath2 = 'productCategory/' . $catitem->id . '/medium/' . $catitem->image_md;
                                $url2 = Storage::url($catImagePath2);
                                $path2 = asset($url2);
                            } else {
                                $path2 = asset('assets/logo/no_img_category_md.jpg');
                            }
                            $tmp1['image_md'] = $path2;

                            $sub_category[] = $tmp1;
                        }
                    }
                }
                $tmp['sub_category'] = $sub_category ?? [];
                $parent['category'][] = $tmp ?? [];
            }
        }
        return new Response(array('error' => 0, 'status_code' => 200, 'message' => 'Data loaded successfully', 'status' => 'success', 'data' => $parent), 200);
    }

    public function getTopBar()
    {
        $data = TopbarContent::where(['enabled' => 1])->limit(1)->get();
        return new Response(array('error' => 0, 'status_code' => 200, 'message' => 'Data loaded successfully', 'status' => 'success', 'data' => $data), 200);
    }

    /**
     * Method getHomeBanners
     *
     * @return void
     */
    public function getHomeBanners()
    {
        $data['top_banners'] = BannerResource::collection(Banner::select('id', 'title', 'description', 'banner_image', 'mobile_banner', 'links', 'banner_type', 'tag_line', 'order_by')->where(['status' => 'published', 'banner_type' => 'main_home'])->limit(4)->orderBy('order_by', 'desc')->get());
        $data['promo_banners'] = BannerResource::collection(Banner::select('id', 'title', 'description', 'banner_image', 'mobile_banner', 'links', 'banner_type', 'tag_line', 'order_by')->where(['status' => 'published', 'banner_type' => 'promo_home'])->orderBy('order_by', 'asc')->get());
        $data['dynamic_brand_category'] = Banner::select('id', 'title AS category_name', 'banner_image', 'links AS link', 'order_by')->where(['status' => 'published', 'banner_type' => 'brand_category'])->orderBy('order_by', 'asc')->get();
        return new Response(array('error' => 0, 'status_code' => 200, 'message' => 'Data loaded successfully', 'status' => 'success', 'data' => $data), 200);
    }

    /**
     * Method getEcomHomeBanners
     *
     * @return void
     */
    public function getEcomHomeDetails()
    {
        $data['top_banners'] = BannerResource::collection(Banner::select('id', 'title', 'description', 'banner_image', 'mobile_banner', 'links', 'banner_type', 'tag_line', 'order_by')->where(['status' => 'published', 'banner_type' => 'ecom_home'])->limit(4)->orderBy('order_by', 'asc')->get());


        $product_categories = ProductCategory::select('id', 'name', 'image', 'slug')->where([['status', 'published'], ['parent_id', '=', 0]])->whereNotNull('image')->limit(9)->orderBy('id', 'desc')->get();
        // foreach ($product_categories as $product_category) {
        //     $imagePath = 'productCategory/' . $product_category->id . '/default/' . $product_category->image;
        //     if (!Storage::exists($imagePath)) {
        //         $path               = asset('assets/logo/no-img-1.png');
        //         $url = Storage::url($imagePath);
        //         $path = asset($url);
        //     } else {
        //         $url = Storage::url($imagePath);
        //         $path = asset($url);
        //     }
        $data['home_categories_new'] = BannerResource::collection(Banner::select('id', 'title', 'description', 'banner_image', 'mobile_banner', 'links', 'banner_type', 'tag_line', 'order_by')->where(['status' => 'published', 'banner_type' => 'home_category_section'])->orderBy('order_by', 'asc')->get());

        //     $home_categories_data[] = ['name' => $product_category->name, 'image' => $path, 'slug' => $product_category->slug];
        // }
        $data['home_categories'] = [
            [
                "name" => "Acoustic Guitars",
                "image" => url('/') . "/storage/brand_category_banner/home_category/guitar.png",
                "slug" => "guitars"
            ],
            [

                "name" => "Electric Drums",
                "image" => url('/'). "/storage/brand_category_banner/home_category/drums.png",
                "slug" => "drums-and-percussions"

            ],
            [
                "name" => "Portable Keyboard",
                "image" => url('/'). "/storage/brand_category_banner/home_category/keyboard.png",
                "slug" => "piano-and-keyboards"
            ],
            [
                "name" => "Saxophone",
                "image" => url('/'). "/storage/brand_category_banner/home_category/saxophone.png",
                "slug" => "woodwind"
            ],
            [
                "name" => "Violin Case",
                "image" => url('/'). "/storage/brand_category_banner/home_category/violin_case.png",
                "slug" => "bows-and-strings"
            ],
            [
                "name" => "Amplifiers",
                "image" => url('/'). "/storage/brand_category_banner/home_category/amplifiers.png",
                "slug" => "pro-audio"
            ],
            [
                "name" => "Headphones",
                "image" => url('/') . "/storage/brand_category_banner/home_category/headphones.png",
                "slug" => "dj-gears"
            ],
            [
                "name" => "Speakers",
                "image" => url('/') . "/storage/brand_category_banner/home_category/speakers.png",
                "slug" => "pro-audio"
            ],
            [
                "name" => "Cables",
                "image" => url('/') . "/storage/brand_category_banner/home_category/cables.png",
                "slug" => "accessories"
            ]
        ];

        $data['promo_banners'] = BannerResource::collection(Banner::select('id', 'title', 'description', 'banner_image', 'mobile_banner', 'links', 'banner_type', 'tag_line', 'order_by')->where(['status' => 'published', 'banner_type' => 'ecom_promo_home'])->orderBy('order_by', 'asc')->get());

        $data['brands'] = Brands::select('id', 'brand_name', 'brand_banner', 'brand_logo', 'short_description', 'notes', 'slug')->withCount(['products' => function ($query) {
            $query->where('status', 'published');
        }])->where(['status' => 'published'])->whereNotNull('brand_logo')->orderBy('products_count', 'desc')->get()->toArray();

        $data['product_collection'] = $this->getHomePageProductCollections();

        return new Response(array('error' => 0, 'status_code' => 200, 'message' => 'Data loaded successfully', 'status' => 'success', 'data' => $data), 200);
    }

    public function getHomePageProductCollections()
    {

        $details        = ProductCollection::where(['show_home_page' => 'yes', 'status' => 'published', 'connected_with_category' => 0])
            ->orderBy('order_by', 'asc')->limit(12)->get();

        $collection     = [];

        if (isset($details) && !empty($details)) {
            foreach ($details as $item) {
                $tmp                    = [];
                $tmp['id']              = $item->id;
                $tmp['collection_name'] = $item->collection_name;
                $tmp['slug']            = $item->slug;
                $tmp['tag_line']        = $item->tag_line;
                $tmp['order_by']        = $item->order_by;

                if (isset($item->collectionProducts) && !empty($item->collectionProducts)) {
                    $i = 0;
                    foreach ($item->collectionProducts as $proItem) {
                        $pro                    = [];
                        // if ($i == 4) {
                        //     break;
                        // }
                        $productInfo            = Product::find($proItem->product_id);
                        if ($productInfo && ($productInfo->status == "published")) {
                            $salePrices             = getProductPrice($productInfo);

                            $pro['id']              = $productInfo->id;
                            $pro['product_name']    = $productInfo->product_name;
                            $pro['hsn_code']        = $productInfo->hsn_code;
                            $pro['product_url']     = $productInfo->product_url;
                            $pro['sku']             = $productInfo->sku;
                            $pro['has_video_shopping'] = $productInfo->has_video_shopping;
                            $pro['stock_status']    = $productInfo->stock_status;
                            $pro['is_featured']     = $productInfo->is_featured;
                            $pro['is_best_selling'] = $productInfo->is_best_selling;
                            $pro['is_new']          = $productInfo->is_new;
                            $pro['sale_prices']     = $salePrices;
                            $pro['mrp_price']       = $productInfo->price;
                            $pro['image']           = $productInfo->base_image;
                            $pro['max_quantity']           = $productInfo->quantity;
                            $pro['category']        = $productInfo->ProductCategory->name ?? null;
                            $pro['category_slug']   = $productInfo->ProductCategory->slug ?? null;
                            $pro['brand_name']      = $productInfo->productBrand->brand_name ?? '';

                            $imagePath              = $productInfo->base_image;

                            if (!Storage::exists($imagePath)) {
                                $path               = asset('assets/logo/no-img-1.png');
                            } else {
                                $url                = Storage::url($imagePath);
                                $path               = asset($url);
                            }

                            $pro['image']           = $path;

                            $tmp['products'][]      = $pro;

                            $i++;
                        }
                    }
                }

                $collection[] = $tmp;
            }
        }
        return $collection;
    }

    /**
     * Method getLoginBanners
     *
     * @return void
     */
    public function getLoginBanners()
    {
        $data = BannerResource::collection(Banner::select('id', 'title', 'description', 'banner_image', 'mobile_banner', 'links', 'banner_type', 'tag_line', 'order_by')->where(['status' => 'published', 'banner_type' => 'login'])->orderBy('order_by', 'asc')->get());
        return new Response(array('error' => 0, 'status_code' => 200, 'message' => 'Data loaded successfully', 'status' => 'success', 'data' => $data), 200);
    }

    public function getBrandPageDynamicData(Request $request)
    {
        $brand_slug = $request->brand_id;
        $brand_data = Brands::where('slug', $brand_slug)->first();
        $brand_id = $brand_data->id;
        $featured_products = Product::withCount('views')->where(['brand_id' => $brand_id, 'is_brand_featured' => 1, 'status' => 'published', 'stock_status' => 'in_stock'])->orderBy('id', 'asc')->limit(5)->get();
        $tmp = [];
        $brands = Brands::find($brand_id);
        $tmp['brand_details'] = $brands;
        $tmp['featured_products'] = $this->getProductsData($featured_products);

        $tmp['top_category_section'] = BrandCategory::where('brand_id', $brand_id)->limit(8)->get();

        $top_selling_products = Product::join('order_products', 'order_products.product_id', '=', 'products.id')
            ->select(['products.*', DB::raw('COUNT(mm_order_products.product_id) as products_count')])
            ->withCount('views')
            ->where(['brand_id' => $brand_id, 'products.status' => 'published'])
            ->groupBy('product_id')
            ->orderBy('products_count', 'desc')
            ->limit(10)
            ->get();

        $tmp['top_selling_products'] = $this->getProductsData($top_selling_products);
        $finalArray = [];
        if (isset($brands)) {
            $content_faq = str_replace(array("\r", "\n"), '', $brands->faq_content);
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
        $tmp['faq_content'] = $finalArray;
        return new Response(array('error' => 0, 'status_code' => 200, 'message' => 'Data loaded successfully', 'status' => 'success', 'data' => $tmp), 200);
    }


    /**
     * Method getProductsData
     *
     * @param $category_products $category_products [explicite description]
     *
     * @return void
     */
    public function getProductsData($category_products)
    {
        $tmp = [];
        foreach ($category_products as $product) {

            $salePrices             = getProductPrice($product);

            $pro                    = [];
            $pro['id']              = $product->id;
            $pro['product_name']    = $product->product_name;
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
            $pro['views_count']    = $product->views_count;
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

    public function getDiscountedProducts(Request $request)
    {
        $discounted_products_collection = Product::with('productDiscount')->get();
        $data = $this->getProductsData($discounted_products_collection);
        return new Response(array('error' => 0, 'status_code' => 200, 'message' => 'Data loaded successfully', 'status' => 'success', 'data' => $data), 200);
    }

    public function getSearchData(Request $request)
    {
        $tmp['popular_choices'] = [
            'view_url' => env('FRONTEND_URL'),
            'data' => [
                [
                    'name' => 'Acoustic Guitars',
                    'image' => url('/') . '/storage/products/67/default/65114d152d81cYHAG0019',
                    'url' => env('FRONTEND_URL') . '/category/guitars/acoustic-guitar-guitars',
                ],
                [
                    'name' => 'Electric Drums',
                    'image' => url('/') . '/storage/products/48/default/65114d0895c6bMXAD0004',
                    'url' => env('FRONTEND_URL') . '/category/drums-and-percussions/electronic-drumkits-drums-and-percussions',
                ],
                [
                    'name' => 'Portable Keyboard',
                    'image' => url('/') . '/storage/products/3/default/65114d1ae1a88YHPK0003',
                    'url' => env('FRONTEND_URL') . '/category/piano-and-keyboards/portable-keyboard-piano-and-keyboards',
                ],
                [
                    'name' => 'Saxophone',
                    'image' => url('/') . '/storage/products/149/default/65114d1d3d317YHWW0086',
                    'url' => env('FRONTEND_URL') . '/category/woodwind/saxophone-woodwind',
                ],
                [
                    'name' => 'Music Stand',
                    'image' => url('/') . '/storage/products/3455/default/2MSCGF1328-1.jpg',
                    'url' => env('FRONTEND_URL') . '/category/accessories/music-stand-accessories',
                ],
                [
                    'name' => 'Drum Kits',
                    'image' => url('/') . '/storage/products/2854/default/2DACTM1157-1.jpg',
                    'url' => env('FRONTEND_URL') . '/category/drums-and-percussions/electronic-drumkits-drums-and-percussions',
                ]
            ]
        ];

        $tmp['top_seller_books'] = [
            'view_url' => env('FRONTEND_URL') . '/category/books/exam-books-books',
            'data' => [
                [
                    'name' => '',
                    'image' => url('/') . '/storage/products/837/default/65114d128afbcTCKB0001',
                    'url' => env('FRONTEND_URL') . '/category/books/exam-books-books',
                ],
                [
                    'name' => '',
                    'image' => url('/') . '/storage/products/838/default/65114d128fa68TCKB0002',
                    'url' => env('FRONTEND_URL') . '/category/books/exam-books-books',
                ],
                [
                    'name' => '',
                    'image' => url('/') . '/storage/products/839/default/65114d12943b2TCKB0003',
                    'url' => env('FRONTEND_URL') . '/category/books/music-books-books',
                ],
                [
                    'name' => '',
                    'image' => url('/') . '/storage/products/840/default/65114d1298e20TCKB0004',
                    'url' => env('FRONTEND_URL') . '/category/books/exam-books-books',
                ],
                [
                    'name' => '',
                    'image' => url('/') . '/storage/products/1181/default/65116bb700919AFRB0019',
                    'url' => env('FRONTEND_URL') . '/category/books/tutors-books-books',
                ]
            ]
        ];

        return new Response(array('error' => 0, 'status_code' => 200, 'message' => 'Data loaded successfully', 'status' => 'success', 'data' => $tmp), 200);
    }
}
