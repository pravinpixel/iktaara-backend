<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductCollectionResource;
use App\Models\Product\Product;
use App\Models\Product\ProductCollection;
use App\Models\RecentView;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class CollectionController extends Controller
{
    public function getProductCollections(Request $request)
    {
        $order_by = $request->order_by;

        $details = ProductCollection::where(['show_home_page' => 'yes', 'status' => 'published', 'can_map_discount' => 'no'])
            ->when($order_by != '', function ($q) use ($order_by) {
                return $q->where('order_by', $order_by);
            })
            ->orderBy('order_by', 'asc')->get();

        return ProductCollectionResource::collection($details);
    }

    public function getProductCollectionByOrder(Request $request)
    {
        $order_by = $request->order_by;


        $details = ProductCollection::where(['show_home_page' => 'yes', 'status' => 'published', 'can_map_discount' => 'no'])
            ->when($order_by != '', function ($q) use ($order_by) {
                return $q->where('order_by', $order_by);
            })
            ->orderBy('order_by', 'asc')->first();

        return ProductCollectionResource::collection($details);
    }

    public function getRecentViews(Request $request)
    {
        if (isset($request->customer_id)) {
            $customer_id = $request->customer_id;

            $recentDetails = RecentView::where('customer_id', $customer_id)->orderBy('created_at', 'desc')->limit(10)->get();
        } else if (isset($request->guest_token)) {
            $guest_token = $request->guest_token;

            $recentDetails = RecentView::where('guest_token', $guest_token)->orderBy('created_at', 'desc')->limit(10)->get();
        }

        $recentData = [];
        if (isset($recentDetails) && !empty($recentDetails)) {
            foreach ($recentDetails as $items) {
                $productInfo = Product::find($items->product_id);

                $category               = $productInfo->productCategory;
                $salePrices             = getProductPrice($productInfo);

                $pro                    = [];
                $pro['has_data']        = 'yes';
                $pro['id']              = $productInfo->id;
                $pro['product_name']    = $productInfo->product_name;
                $pro['category_name']   = $category->name ?? '';
                $pro['category_slug']   = $category->slug ?? '';
                $pro['parent_category_name']   = $category->parent->name ?? '';
                $pro['parent_category_slug']   = $category->parent->slug ?? '';
                $pro['brand_name']      = $productInfo->productBrand->brand_name ?? '';
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
                $pro['videolinks']      = $productInfo->productVideoLinks;
                $pro['links']           = $productInfo->productLinks;
                $pro['image']           = $productInfo->base_image;
                $pro['max_quantity']    = $productInfo->quantity;

                $imagePath              = $productInfo->base_image;

                if (!Storage::exists($imagePath)) {
                    $path               = asset('assets/logo/no-img-1.png');
                } else {
                    $url                = Storage::url($imagePath);
                    $path               = asset($url);
                }

                $pro['image']                   = $path;
                $recentData[] = $pro;
                // print_r( count($items->products) );
            }


            $tmp['recently_viewed_products'] = $recentData;
        }
        return response()->json(array('error' => 0, 'status_code' => 200, 'message' => 'Data loaded successfully', 'status' => 'success', 'data' => $tmp), 200);
    }

    public function getTopSellingProducts()
    {
        $top_selling_products = Product::join('order_products', 'order_products.product_id', '=', 'products.id')
            ->select(['products.*', DB::raw('COUNT(mm_order_products.product_id) as products_count')])
            ->where(['products.status' => 'published'])
            ->groupBy('product_id')
            ->orderBy('products_count', 'desc')
            ->limit(10)
            ->get();

        $tmp['top_selling_products'] = $this->getProductsData($top_selling_products);
        return response()->json(array('error' => 0, 'status_code' => 200, 'message' => 'Data loaded successfully', 'status' => 'success', 'data' => $tmp), 200);
    }


    /**
     * Method productCollectionByColorCode
     *
     * @param Request $request [explicite description]
     *
     * @return void
     */
    public function productCollectionByColorCode(Request $request)
    {
        $category_id = $request->category_id;
        $colors = ['#00968A', '#231F58', '#E34061', '#F28BA9', '#512A96', '#6B1579'];
        try {
            $details = ProductCollection::where(['connected_with_category' => 1, 'status' => 'published', 'category_id' => $category_id])
                ->orderBy('order_by', 'desc')->get();
            $data = [];
            foreach ($details as $detail) {
                $data[] = [
                    'title' => $detail->collection_name,
                    'price_range_text' => $detail->tag_line,
                    'random_color' => $colors[array_rand($colors)]
                ];
            }
            return response()->json(array('error' => 0, 'status_code' => 200, 'message' => 'Data loaded successfully', 'status' => 'success', 'data' => $data), 200);
        } catch (Exception $e) {
            return response()->json(array('error' => 1, 'status_code' => 200, 'message' => $e->getMessage(), 'status' => 'failure', 'data' => []), 200);
        }
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
}
