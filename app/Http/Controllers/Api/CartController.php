<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\CartAddress;
use App\Models\CartShiprocketResponse;
use App\Models\Master\Customer;
use App\Models\Master\CustomerAddress;
use App\Models\Master\Pincode;
use App\Models\MerchantProduct;
use App\Models\Product\Product;
use App\Models\Product\ProductMeasurement;
use App\Models\Seller\MerchantShopsData;
use App\Models\Settings\Tax;
use App\Models\ShippingCharge;
use App\Services\ShipRocketService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Seshac\Shiprocket\Shiprocket;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CartController extends Controller
{

    public function addToCart(Request $request, ShipRocketService $service)
    {
        if (Auth()->guard('api')->check()) {
            $customer_id = auth()->guard('api')->user()->id;
        } else {
            $customer_id = null;
        }
        // $customer_id = $request->customer_id;
        $guest_token = $request->guest_token;
        $product_id = $request->id;
        $quantity = $request->quantity;
        $type = $request->type;
        $salePrices = $request->sale_prices;

        /**
         * 1. check customer id and product exist if not insert
         * 2. if exist update quantiy
         */

        $product_info = Product::find($product_id);
        if (!$product_info) {
            return response()->json(array('error' => 1, 'status_code' => 400, 'message' => 'Product Data not available.Contact Administrator', 'status' => 'failed', 'data' => []), 400);
        }
        $checkCart = Cart::when($customer_id != '', function ($q) use ($customer_id) {
            $q->where('customer_id', $customer_id);
        })->when($customer_id == '' && $guest_token != '', function ($q) use ($guest_token) {
            $q->where('token', $guest_token);
        })->where('product_id', $product_id)->first();

        $getCartToken = Cart::when($customer_id != '', function ($q) use ($customer_id) {
            $q->where('customer_id', $customer_id);
        })->when($customer_id == '' && $guest_token != '', function ($q) use ($guest_token) {
            $q->where('token', $guest_token);
        })->first();


        if (isset($checkCart) && !empty($checkCart)) {
            if ($type == 'delete') {
                $checkCart->delete();
            } else {
                $product_quantity = $checkCart->quantity + $quantity;
                if ($product_info->quantity <= $product_quantity) {
                    $product_quantity = $product_info->quantity;
                }

                $checkCart->quantity  = $product_quantity;
                $checkCart->sub_total = $product_quantity * $checkCart->price;
                $checkCart->update();
            }
        } else {
            $customer_info = Customer::find($customer_id);
            if (isset($customer_info) && !empty($customer_info) || !empty($request->guest_token)) {
                if ($product_info->quantity <= $quantity) {
                    $quantity = $product_info->quantity;
                }
                $ins['customer_id']     = $customer_id;
                $ins['product_id']      = $product_id;
                $ins['guest_token']     = $getCartToken->guest_token ?? 'ORD' . date('ymdhis');
                $ins['quantity']        = $quantity ?? 1;
                $ins['price']           = (isset($salePrices)) ? (float)$salePrices['price_original'] : 1;
                $ins['sub_total']       = (isset($salePrices)) ? $salePrices['price_original'] * $quantity ?? 1 : 1;
                $ins['token']           = $request->guest_token ?? null;

                $cart_id = Cart::create($ins)->id;

                $ins['message']         = 'added';
            } else {
                return response()->json(array('error' => 1, 'status_code' => 400, 'message' => 'Customer Data not available.Contact Administrator', 'status' => 'failed', 'data' => []), 400);

                // return array('error' => 1, 'message' => 'Customer Data not available.Contact Administrator');
            }
            // if (!isset($customer_info) && empty($customer_info) || empty($request->guest_token)) {
            //     return response()->json(array('error' => 1, 'status_code' => 400, 'message' => 'Customer Data not available.Contact Administrator', 'status' => 'failed', 'data' => []), 400);
            // }

            // if ($product_info->quantity <= $quantity) {
            //     $quantity = $product_info->quantity;
            // }
            // $ins['customer_id']     = $request->customer_id;
            // $ins['product_id']      = $product_id;
            // $ins['guest_token']     = $getCartToken->guest_token ?? 'ORD' . date('ymdhis');
            // $ins['quantity']        = $quantity ?? 1;
            // $ins['price']           = (isset($salePrices)) ? (float)$salePrices['price_original'] : 1;
            // $ins['sub_total']       = (isset($salePrices)) ? $salePrices['price_original'] * $quantity ?? 1 : 1;
            // $ins['token']           = $request->guest_token ?? null;
            // $ins['message']         = 'added';

            // Cart::create($ins)->id;
        }


        return response()->json(array('error' => 0, 'status_code' => 200, 'message' => 'Product added to cart successfully', 'status' => 'success', 'data' => $this->getCartListAll($customer_id, null, $guest_token)), 200);
    }

    public function bulkAddToCart(Request $request, ShipRocketService $service)
    {
        if (Auth()->guard('api')->check()) {
            $customer_id = auth()->guard('api')->user()->id;
        } else {
            $customer_id = null;
        }
        $guest_token = $request->guest_token;
        $type = $request->type;
        $quantity = 1;
        $products = $request->products;

        /**
         * 1. check customer id and product exist if not insert
         * 2. if exist update quantiy
         */
        foreach ($products as $product) {

            $product_info = Product::find($product['id']);
            if (!$product_info) {
                return response()->json(array('error' => 1, 'status_code' => 400, 'message' => 'Product Data not available.Contact Administrator', 'status' => 'failed', 'data' => []), 400);
            }
            $checkCart = Cart::when($customer_id != '', function ($q) use ($customer_id) {
                $q->where('customer_id', $customer_id);
            })->when($customer_id == '' && $guest_token != '', function ($q) use ($guest_token) {
                $q->where('token', $guest_token);
            })->where('product_id', $product['id'])->first();

            $getCartToken = Cart::when($customer_id != '', function ($q) use ($customer_id) {
                $q->where('customer_id', $customer_id);
            })->when($customer_id == '' && $guest_token != '', function ($q) use ($guest_token) {
                $q->where('token', $guest_token);
            })->first();


            if (isset($checkCart) && !empty($checkCart)) {
                if ($type == 'delete') {
                    $checkCart->delete();
                } else {
                    $product_quantity = $checkCart->quantity + $quantity;
                    if ($product_info->quantity <= $product_quantity) {
                        $product_quantity = $product_info->quantity;
                    }

                    $checkCart->quantity  = $product_quantity;
                    $checkCart->sub_total = $product_quantity * $checkCart->price;
                    $checkCart->update();
                }
            } else {
                $customer_info = Customer::find($customer_id);
                if (isset($customer_info) && !empty($customer_info) || !empty($request->guest_token)) {
                    if ($product_info->quantity <= $quantity) {
                        $quantity = $product_info->quantity;
                    }
                    $ins['customer_id']     = $customer_id;
                    $ins['product_id']      = $product['id'];
                    $ins['guest_token']     = $getCartToken->guest_token ?? 'ORD' . date('ymdhis');
                    $ins['quantity']        = $quantity ?? 1;
                    $ins['price']           = $product['price_original'];
                    $ins['sub_total']       = $product['price_original'] * $quantity;
                    $ins['token']           = $request->guest_token ?? null;

                    $cart_id = Cart::create($ins)->id;

                    $ins['message']         = 'added';
                } else {
                    return response()->json(array('error' => 1, 'status_code' => 400, 'message' => 'Customer Data not available.Contact Administrator', 'status' => 'failed', 'data' => []), 400);

                    // return array('error' => 1, 'message' => 'Customer Data not available.Contact Administrator');
                }
            }
        }

        return response()->json(array('error' => 0, 'status_code' => 200, 'message' => 'Products added to cart successfully', 'status' => 'success', 'data' => $this->getCartListAll($customer_id, null, $guest_token)), 200);
    }

    public function updateCart(Request $request, ShipRocketService $service)
    {
        if (Auth()->guard('api')->check()) {
            $customer_id = auth()->guard('api')->user()->id;
        } else {
            $customer_id = null;
        }
        $cart_id        = $request->cart_id;
        $guest_token    = $request->guest_token;
        // $customer_id    = $request->customer_id;
        $quantity       = $request->quantity;
        $customer_info = Customer::find($customer_id);


        if (isset($customer_info) && !empty($customer_info) || !empty($request->guest_token)) {
            $checkCart      = Cart::where('id', $cart_id)->first();
            // $service->getShippingRocketOrderDimensions($checkCart->customer_id);
            // dd( 'service');

            $checkCart->quantity = $quantity;
            $checkCart->sub_total = $checkCart->price * $quantity;
            $checkCart->update();

            $shiprocket_charges = $service->getShippingRocketOrderDimensions($customer_id, $service->getToken(), $guest_token);
            return response()->json(array('error' => 0, 'status_code' => 200, 'message' => 'Cart updated successfully', 'status' => 'success', 'data' => $this->getCartListAll($checkCart->customer_id, null, $guest_token)), 200);
        } else {
            return response()->json(array('error' => 1, 'status_code' => 400, 'message' => 'Customer Data not available.Contact Administrator', 'status' => 'failed', 'data' => []), 400);
        }
    }

    public function deleteCart(Request $request)
    {
        if (Auth()->guard('api')->check()) {
            $customer_id = auth()->guard('api')->user()->id;
        } else {
            $customer_id = null;
        }
        $cart_id        = $request->cart_id;
        $guest_token    = $request->guest_token;
        $checkCart      = Cart::find($cart_id);
        if (!$checkCart) {
            return response()->json(array('error' => 1, 'status_code' => 400, 'message' => 'Cart not found', 'status' => 'failed', 'data' => []), 400);
        }
        // $customer_id = $checkCart->customer_id;
        $checkCart->delete();

        return response()->json(array('error' => 1, 'status_code' => 200, 'message' => 'Item deleted successfully', 'status' => 'success', 'data' => $this->getCartListAll($customer_id, null, $guest_token, null, null, null)), 200);
    }

    public function clearCart(Request $request)
    {
        if (Auth()->guard('api')->check()) {
            $customer_id = auth()->guard('api')->user()->id;
        } else {
            $customer_id = '';
        }
        // $customer_id        = $request->customer_id;
        $guest_token        = $request->guest_token;

        Cart::when($customer_id != '', function ($q) use ($customer_id) {
            $q->where('customer_id', $customer_id);
        })->when($customer_id == '' && $guest_token != '', function ($q) use ($guest_token) {
            $q->where('token', $guest_token);
        })->delete();

        if ($customer_id) {
            CartAddress::where('customer_id', $customer_id)->delete();
        }

        return response()->json(array('error' => 1, 'status_code' => 200, 'message' => 'Cart cleared successfully', 'status' => 'success', 'data' => $this->getCartListAll($customer_id, null, $guest_token)), 200);
    }

    public function getCarts(Request $request)
    {
        if (Auth()->guard('api')->check()) {
            $customer_id = auth()->guard('api')->user()->id;
        } else {
            $customer_id = '';
        }
        $guest_token = $request->guest_token;
        $tmp                = [];
        if ($guest_token == null) {
            $tmp['carts'] = [];
            $tmp['cart_count'] = 0;
            $tmp['shipping_charges']    = [];
            $tmp['cart_total']          = array(
                'total' => 0.00,
                'product_tax_exclusive_total' =>  0.00,
                'product_tax_exclusive_total_without_format' => 0,
                'tax_total' =>  0.00,
                'tax_percentage' =>  0.00,
                'shipping_charge' =>  0.00
            );
            return response()->json(array('error' => 0, 'status_code' => 200, 'message' => 'Data loaded successfully', 'status' => 'success', 'data' => $tmp), 200);
        }
        // $customer_id    = $request->customer_id;
        $selected_shipping = $request->selected_shipping ?? '';

        return response()->json(array('error' => 0, 'status_code' => 200, 'message' => 'Data loaded successfully', 'status' => 'success', 'data' => $this->getCartListAll($customer_id, null, $guest_token, null, $selected_shipping)), 200);
    }

    function getCartListAll($customer_id = null, $shipping_info = null, $guest_token = null, $shipping_type = null, $selected_shipping = null, $coupon_data = null)
    {
        // dd( $coupon_data );
        $checkCart          = Cart::when($customer_id != '', function ($q) use ($customer_id) {
            $q->where('customer_id', $customer_id);
        })->when($customer_id == '' && $guest_token != '', function ($q) use ($guest_token) {
            $q->where('token', $guest_token);
        })->get();

        $tmp                = [];
        $grand_total        = 0;
        $tax_total          = 0;
        $product_tax_exclusive_total = 0;
        $tax_percentage = 0;
        $cartTmp = [];
        if (isset($checkCart) && !empty($checkCart)) {
            foreach ($checkCart as $citems) {
                $items = $citems->products;
                $tax = [];
                $tax_percentage = 0;

                $category               = isset($items) ? $items->productCategory : '';
                $salePrices             = getProductPrice($items);

                if (isset($category->parent->tax_id) && !empty($category->parent->tax_id)) {
                    $tax_info = Tax::find($category->parent->tax_id);
                } else if (isset($category->tax_id) && !empty($category->tax_id)) {
                    $tax_info = Tax::find($category->tax_id);
                }
                // dump( $citems );
                if (isset($tax_info) && !empty($tax_info)) {
                    $tax = getAmountExclusiveTax($salePrices['price_original'], $tax_info->pecentage);
                    $tax_total =  $tax_total + ($tax['gstAmount'] * $citems->quantity) ?? 0;
                    $product_tax_exclusive_total = $product_tax_exclusive_total + ($tax['basePrice'] * $citems->quantity);
                    // print_r( $product_tax_exclusive_total );
                    $tax_percentage         = $tax['tax_percentage'] ?? 0;
                } else {
                    $product_tax_exclusive_total = $product_tax_exclusive_total + $citems->sub_total;
                }

                $pro                    = [];
                $pro['id']              = isset($items) ? $items->id : null;
                $pro['tax']             = $tax;
                $pro['tax_percentage']  = $tax_percentage;
                $pro['hsn_no']          = $items->hsn_code ?? null;
                $pro['product_name']    = isset($items) ? $items->product_name : null;
                $pro['category_name']   = $category->name ?? '';
                $pro['brand_name']      = $items->productBrand->brand_name ?? '';
                $pro['hsn_code']        = isset($items) ? $items->hsn_code : null;
                $pro['product_url']     = isset($items) ? $items->product_url : null;
                $pro['sku']             = isset($items) ? $items->sku : null;
                $pro['has_video_shopping'] = isset($items) ? $items->has_video_shopping : null;
                $pro['stock_status']    = isset($items) ? $items->stock_status : null;
                $pro['is_featured']     = isset($items) ? $items->is_featured : null;
                $pro['is_best_selling'] = isset($items) ? $items->is_best_selling : null;
                $pro['is_new']          = isset($items) ? $items->is_new : null;
                $pro['sale_prices']     = isset($items) ? $salePrices : null;
                $pro['mrp_price']       = isset($items) ? $items->price : null;
                $pro['image']           = isset($items) ? $items->base_image : null;
                $pro['max_quantity']    = isset($items) ? $items->quantity : null;
                $imagePath              = isset($items) ? $items->base_image : null;

                if (!Storage::exists($imagePath)) {
                    $path               = asset('assets/logo/product-noimg.png');
                } else {
                    $url                = Storage::url($imagePath);
                    $path               = asset($url);
                }

                $pro['image']           = $path;
                $pro['customer_id']     = $customer_id;
                $pro['guest_token']     = $citems->token;
                $pro['cart_id']         = $citems->id;
                $pro['price']           = $citems->price;
                $pro['quantity']        = $citems->quantity;
                $pro['sub_total']       = $citems->sub_total;
                $pro['shiprocket_order_id'] = $citems->guest_token;
                $grand_total            += $citems->sub_total;
                $cartTmp[] = $pro;
            }

            $tmp['carts'] = $cartTmp;
            $tmp['cart_count'] = count($cartTmp);
            if (isset($shipping_info) && (!empty($shipping_info) && $shipping_type != 'flat')  || (isset($selected_shipping) && !empty($selected_shipping) && $shipping_type != 'flat')) {
                $tmp['selected_shipping_fees'] = array(
                    'shipping_id' => $shipping_info->id ?? $selected_shipping['shipping_id'],
                    'shipping_charge_order' => $shipping_info->charges ?? $selected_shipping['shipping_charge_order'],
                    'shipping_type' => $shipping_type ?? $selected_shipping['shipping_type'] ?? 'fees'
                );

                $grand_total                = $grand_total + ($shipping_info->charges ?? $selected_shipping['shipping_charge_order'] ?? 0);
            } else if ($shipping_type == 'flat' && $shipping_info) {
                $tmp['selected_shipping_fees'] = array(
                    'shipping_id' => $shipping_info['flat_charge'],
                    'shipping_charge_order' => $shipping_info['flat_charge'],
                    'shipping_type' => $shipping_type
                );
                $grand_total                = $grand_total + ($shipping_info['flat_charge'] ?? 0);
            }
            if (isset($coupon_data) && !empty($coupon_data)) {
                $grand_total = $grand_total - $coupon_data['discount_amount'] ?? 0;
            }

            $amount         = filter_var($grand_total, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
            $charges        = ShippingCharge::where('status', 'published')->where('minimum_order_amount', '<', $amount)->get();

            $tmp['shipping_charges']    = $charges;
            $tmp['cart_total']          = array(
                'total' => number_format(round($grand_total), 2),
                'product_tax_exclusive_total' => number_format(round($product_tax_exclusive_total), 2),
                'product_tax_exclusive_total_without_format' => round($product_tax_exclusive_total),
                'tax_total' => number_format(round($tax_total), 2),
                'tax_percentage' => number_format(round($tax_percentage), 2),
                'shipping_charge' => $shipping_info->charges ?? $shipping_info['flat_charge'] ?? 0
            );
        }

        return $tmp;
    }

    public function getShippingCharges(Request $request)
    {
        $amount         = filter_var($request->amount, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
        $charges        = ShippingCharge::where('status', 'published')->where('minimum_order_amount', '<', $amount)->get();

        return response()->json(array('error' => 0, 'status_code' => 200, 'message' => 'Data loaded successfully', 'status' => 'success', 'data' => $charges), 200);
    }

    public function updateCartAmount(Request $request)
    {
        if (Auth()->guard('api')->check()) {
            $customer_id = auth()->guard('api')->user()->id;
        } else {
            $customer_id = '';
        }
        // $customer_id    = $request->customer_id;
        $shipping_id    = $request->shipping_id;
        $type    = $request->type;
        $coupon_data = $request->coupon_data ?? '';


        if (isset($type) && !empty($type) && $type == 'rocket') {
            $cartInfo = Cart::where('customer_id', $customer_id)->first();

            if (isset($cartInfo->rocketResponse->shipping_charge_response_data) && !empty($cartInfo->rocketResponse->shipping_charge_response_data)) {
                $response = json_decode($cartInfo->rocketResponse->shipping_charge_response_data);
                $tmp = [];
                if (isset($response->data->available_courier_companies) && !empty($response->data->available_courier_companies)) {
                    foreach ($response->data->available_courier_companies as $tiem) {
                        if ($tiem->id == $shipping_id) {
                            $tmp = $tiem;
                            break;
                        }
                    }
                }
                if ($tmp) {
                    $amount = array(
                        (float)$tmp['coverage_charges'],
                        (float)$tmp['freight_charge'],
                        (float)$tmp['rate'],
                        (float)$tmp['rto_charges']
                    );
                    $shipping_info['charges'] = getSecondLevelCharges($amount);
                    $shipping_info['id'] = $shipping_id;
                    $shipping_info = (object)$shipping_info;
                } else {
                    $shipping_info  = ShippingCharge::find($shipping_id);
                }
            }
        } else if (isset($type) && !empty($type) && $type == 'flat') {
            $shipping_info = array('flat_charge' => $shipping_id);
        } else {

            $shipping_info  = ShippingCharge::find($shipping_id);
        }
        return response()->json(array('error' => 0, 'status_code' => 200, 'message' => 'Data loaded successfully', 'status' => 'success', 'data' => $this->getCartListAll($customer_id, $shipping_info, null, $type, null, $coupon_data)), 200);
    }

    public function getShippingRocketCharges(Request $request, ShipRocketService $service)
    {

        $from_type = $request->from_type;
        $address = $request->address;
        $shippingAddress = CustomerAddress::find($address);
        if (Auth()->guard('api')->check()) {
            $customer_id = auth()->guard('api')->user()->id;
        } else {
            $customer_id = '';
        }

        $cart_info = Cart::where('customer_id', $customer_id)->first(); //get from token
        /**
         * get volume metric value for kg
         */
        $all_cart = Cart::where('customer_id', $customer_id)->get();
        $flat_charges = [];
        $overall_flat_charges = 0;
        // dd( $all_cart );
        if (isset($all_cart) && !empty($all_cart)) {
            foreach ($all_cart as $item) {
                $flat_charges[] = getVolumeMetricCalculation($item->products->productMeasurement->length ?? 0, $item->products->productMeasurement->width ?? 0, $item->products->productMeasurement->hight ?? 0);
            }
        }
        if (!empty($flat_charges)) {

            $volume_metric_weight = max($flat_charges);

            $overall_flat_charges = $volume_metric_weight * gSetting('flat_charge') ?? 0;
        }

        /**
         *  End Metric value calculation
         */
        if (isset($from_type) && !empty($from_type)) {

            CartAddress::where('customer_id', $customer_id)
                ->where('address_type', $from_type)->delete();
            $ins_cart = [];
            $ins_cart['cart_token'] = $cart_info->guest_token;
            $ins_cart['customer_id'] = $customer_id;
            $ins_cart['address_type'] = $from_type;
            $ins_cart['name'] = isset($shippingAddress->name) ? $shippingAddress->name : 'No name';
            $ins_cart['email'] = $shippingAddress->email;
            $ins_cart['mobile_no'] = $shippingAddress->mobile_no;
            $ins_cart['address_line1'] = $shippingAddress->address_line1;
            $ins_cart['country'] = 'india';
            $ins_cart['post_code'] = $shippingAddress->post_code;
            $ins_cart['state'] = $shippingAddress->state;
            $ins_cart['city'] = $shippingAddress->city;

            $cart_address = CartAddress::create($ins_cart);
            // $data = $service->getShippingRocketOrderDimensions($customer_id, $cart_info->guest_token ?? null, $cart_address->id);
        }
        // if (isset($data)) {
        //     $shipping_charge = $data;
        //     Log::debug("got the response from api for cart id " . $shipping_charge);
        // } else {
            $shipping_charge = round($overall_flat_charges);
            // Log::debug("did not get the response from api for cart id, calculated shipping charge based on volumetric calculation - " . $cart_info->id);
            // Log::debug("overall flat charge" . $overall_flat_charges);
        // }
        $chargeData =  array('shiprocket_charges' => 0, 'flat_charge' => $shipping_charge);

        return response()->json(array('error' => 0, 'flat_charges'=> $flat_charges ,'status_code' => 200, 'message' => 'Data loaded successfully', 'status' => 'success', 'data' => $chargeData), 200);
    }

    public function getDeliveryCharges(Request $request)
    {
        $pincode = $request->pincode;
        $product_id = $request->product_id;
        $price = $request->price;
        $state = Pincode::where('pincode', $pincode)->first();
        if (!$state) {
            return response()->json(array('error' => 0, 'status_code' => 400, 'message' => 'Pincode not exists', 'status' => 'failure', 'data' => []), 400);
        }
        $state_id = $state->id;
        $quantity = 1;
        $merchant_post_code = $this->getMerchantPostCode($product_id, $state_id, $quantity);
        $token =  Shiprocket::getToken();
        $product_measurement = ProductMeasurement::where('product_id', $product_id)->first();
        $pincodeDetails = array(
            "pickup_postcode" => $merchant_post_code,
            "delivery_postcode" => $pincode,
            "cod" =>  false,
            "weight" => $product_measurement->weight,
            "length" => $product_measurement->length,
            "breadth" => $product_measurement->breadth,
            "height" => $product_measurement->height,
            "declared_value" => $price,
            "mode" => "Surface",
            "is_return" => 0,
            "couriers_type" => 0,
            "only_local" => 0
        );
        $shiprocket_response = Shiprocket::courier($token)->checkServiceability($pincodeDetails);
        $response = json_decode($shiprocket_response);
        // dd($response);
        $delivery_data = [];
        if (isset($response->data->available_courier_companies) && !empty($response->data->available_courier_companies)) {
            $courier_data = end($response->data->available_courier_companies);
            $delivery_data['amount'] = $courier_data->freight_charge;
            $delivery_data['courier_name'] = $courier_data->courier_name;
            $delivery_data['estimated_delivery'] = $courier_data->etd;
        } else {
            return response()->json(array('error' => 0, 'status_code' => 400, 'message' => $response->message, 'status' => 'failure', 'data' => []), 400);
        }
        return response()->json(array('error' => 0, 'status_code' => 200, 'message' => 'Data loaded successfully', 'status' => 'success', 'data' => $delivery_data), 200);
    }

    public function getMerchantPostCode($product_id, $customer_state_id, $quantity)
    {
        $customer_zone = getZoneByStateId($customer_state_id);
        $customer_zone_id = $customer_zone['id'];
        $customer_zone_order = $customer_zone['order'];
        $merchant_zone_order = [];
        $merchant_item_order = MerchantProduct::join('merchants', 'merchants.id', '=', 'merchant_products.merchant_id')->where('merchants.status', 'approved')->where('merchants.mode', 'active')->where('product_id', $product_id)->where('qty', '>=', $quantity)->where('merchant_products.status', 'published')->where('zone_id', $customer_zone_id)->orderBy(DB::raw('ISNULL(priority), priority'), 'ASC')->first();
        if ($merchant_item_order) {
            $merchant_post_code_id = $merchant_item_order->pincode_id;
            $merchant_post_code = Pincode::find($merchant_post_code_id)->pincode;
        } else {
            $merchant_zone_data = MerchantProduct::join('merchants', 'merchants.id', '=', 'merchant_products.merchant_id')->leftJoin('zones', 'zones.id', '=', 'merchants.zone_id')->where('merchants.status', 'approved')->where('merchants.mode', 'active')->where('product_id', $product_id)->where('qty', '>=', $quantity)->where('merchant_products.status', 'published')->get(); //->pluck('zone_order', 'merchant_shops_data.pincode_id');
            foreach ($merchant_zone_data as $merchant_zone) {
                $merchant_zone_order[$merchant_zone->merchant_id] = $merchant_zone->zone_order;
            }
            if ($merchant_zone_data) {
                $sorted_zone_order = call_user_func(function (array $a) {
                    asort($a);
                    return $a;
                }, $merchant_zone_order);
                $merchant_id = array_search($customer_zone_order, $sorted_zone_order);
                if ($merchant_id) {
                    $merchant = MerchantShopsData::where('merchant_id', $merchant_id)->first();
                    $merchant_post_code_id = $merchant->pincode_id;
                    $merchant_post_code = Pincode::find($merchant_post_code_id)->pincode;
                } else {
                    $merchant_post_code = 600002;
                }
            } else {
                $merchant_post_code = 600002;
            }
        }
        return $merchant_post_code;
    }
}
