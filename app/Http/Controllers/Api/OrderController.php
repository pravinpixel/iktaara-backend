<?php

namespace App\Http\Controllers\Api;

use App\Events\CancelOrder;
use App\Events\ProductExchange;
use App\Http\Controllers\Controller;
use App\Models\Master\OrderStatus;
use App\Models\MerchantOrder;
use App\Services\ShipRocketService;
use App\Models\Order;
use App\Models\OrderCancelReason;
use App\Models\OrderHistory;
use App\Models\OrderProduct;
use App\Models\Reviews;
use Illuminate\Http\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use App\Models\OrderExchange;
use App\Models\Payment;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class OrderController extends Controller
{

    public function getOrderItemDetail(Request $request)
    {
        $itemId = $request->item_id;
        $orderedProduct = OrderProduct::find($itemId);
        if ($orderedProduct) {
            $pro = OrderProduct::select('order_products.*', 'orders.order_no')
                ->leftJoin('orders', 'orders.id', '=', 'order_products.order_id')
                ->where('order_products.id', $itemId)->first();
            $tmp1['item_id'] = $pro->id;
            $tmp1['product_id'] = $pro->product_id;
            $tmp1['order_id'] = $pro->order_id;
            $tmp1['order_no'] = $pro->order_no;
            $tmp1['product_name'] = $pro->product_name;
            $tmp1['hsn_code'] = $pro->hsn_code;
            $tmp1['sku'] = $pro->sku;
            $tmp1['quantity'] = $pro->quantity;
            $tmp1['price'] = $pro->price;
            $tmp1['tax_amount'] = $pro->tax_amount;
            $tmp1['tax_percentage'] = $pro->tax_percentage;
            $tmp1['quantity'] = $pro->quantity;
            $tmp1['sub_total'] = $pro->sub_total;
            $tmp1['status'] = $pro->getStatus->status_name;
            $tmp1['status_id'] = $pro->status;
            $tmp1['created_at'] = date('d-M-Y H:i:s', strtotime($pro->created_at));
            $tmp1['updated_at'] = date('d-M-Y H:i:s', strtotime($pro->updated_at));
            $imagePath              = (isset($pro->products)) ? $pro->products->base_image : '';
            if (!Storage::exists($imagePath)) {
                $path               = asset('assets/logo/no-img-1.png');
            } else {
                $url                = Storage::url($imagePath);
                $path               = asset($url);
            }
            $tmp1['image']                   = $path;

            return new Response(array('error' => 0, 'status_code' => 200, 'message' => 'Data loaded successfully', 'status' => 'success', 'data' => $tmp1), 200);
        } else {
            return new Response(array('error' => 1, 'status_code' => 200, 'message' => 'Please Try Again', 'status' => 'failed', 'data' => []), 200);
        }
    }

    public function getOrders(Request $request)
    {
        $sort = isset($request->sort) ? $request->sort : 'desc';
        $cancel_status = $request->status;
        $i = 0;
        $orders = [];

        if ($cancel_status == 'cancel') {
            $orderAll = Order::join('order_products', 'orders.id', '=', 'order_products.order_id')
                ->where('orders.customer_id', auth()->guard('api')->user()->id)
                ->whereIn('order_products.status', [3, 6])
                ->select([DB::RAW('DISTINCT(mm_orders.id)'), 'orders.*'])
                // ->select('orders.*', 'order_products.status AS order_item_status')
                ->orderBy('order_products.updated_at', $sort)
                ->get();
            foreach ($orderAll as $info) {
                $order_data = Order::find($info->id);
                $tmp['id'] = $order_data->id;
                $tmp['order_no'] = $order_data->order_no;
                $tmp['shipping_type'] = $order_data->shipping_type;
                $tmp['amount'] = $order_data->amount;
                $tmp['tax_percentage'] = $order_data->tax_percentage;
                $tmp['tax_amount'] = $order_data->tax_amount;
                $tmp['shipping_amount'] = $order_data->shipping_amount;
                $tmp['discount_amount'] = $order_data->discount_amount;
                $tmp['coupon_amount'] = $order_data->coupon_amount;
                $tmp['coupon_code'] = $order_data->coupon_code;
                $tmp['sub_total'] = $order_data->sub_total;
                $tmp['billing_name'] = $order_data->billing_name;
                $tmp['billing_email'] = $order_data->billing_email;
                $tmp['billing_mobile_no'] = $order_data->billing_mobile_no;
                $tmp['billing_address_line1'] = $order_data->billing_address_line1;
                $tmp['billing_address_line2'] = $order_data->billing_address_line2;
                $tmp['billing_landmark'] = $order_data->billing_landmark;
                $tmp['billing_country'] = $order_data->billing_country;
                $tmp['billing_post_code'] = $order_data->billing_post_code;
                $tmp['billing_state'] = $order_data->billing_state;
                $tmp['billing_city'] = $order_data->billing_city;

                $tmp['shipping_name'] = $order_data->shipping_name;
                $tmp['shipping_email'] = $order_data->shipping_email;
                $tmp['shipping_mobile_no'] = $order_data->shipping_mobile_no;
                $tmp['shipping_address_line1'] = $order_data->shipping_address_line1;
                $tmp['shipping_address_line2'] = $order_data->shipping_address_line2;
                $tmp['shipping_landmark'] = $order_data->shipping_landmark;
                $tmp['shipping_country'] = $order_data->shipping_country;
                $tmp['shipping_post_code'] = $order_data->shipping_post_code;
                $tmp['shipping_state'] = $order_data->shipping_state;
                $tmp['shipping_city'] = $order_data->shipping_city;
                $tmp['invoice_file'] = asset('storage/invoice_order/' . $order_data->order_no . '.pdf');
                $tmp['order_date'] = date('d-M-Y H:i:s', strtotime($order_data->created_at));
                $payments = Payment::where('order_id', $order_data->id)->first();
                $response = isset($payments) ? unserialize($payments->response) : '';
                $tmp['payment_mode'] = isset($response['payment_mode']) ? $response['payment_mode'] : '';
                $tmp['card_name'] = isset($response['card_name']) ? $response['card_name'] : '';
                $itemArray = [];
                if (isset($order_data->orderItems) && !empty($order_data->orderItems)) {
                    foreach ($order_data->orderItems as $pro) {
                        if ($pro->status == 3 || $pro->status == 6) {
                            $tmp1 = [];
                            $tmp1['item_id'] = $pro->id;
                            $tmp1['product_id'] = $pro->product_id;
                            $tmp1['order_id'] = $pro->order_id;
                            $tmp1['order_no'] = $info->order_no;
                            $tmp1['product_name'] = $pro->product_name;
                            $tmp1['hsn_code'] = $pro->hsn_code;
                            $tmp1['sku'] = $pro->sku;
                            $tmp1['quantity'] = $pro->quantity;
                            $tmp1['price'] = $pro->price;
                            $tmp1['tax_amount'] = $pro->tax_amount;
                            $tmp1['tax_percentage'] = $pro->tax_percentage;
                            $tmp1['quantity'] = $pro->quantity;
                            $tmp1['sub_total'] = $pro->sub_total;
                            $tmp1['status'] = $pro->getStatus->status_name;
                            $tmp1['status_id'] = $pro->status;
                            $tmp1['created_at'] = date('d-M-Y H:i:s', strtotime($pro->created_at));
                            $tmp1['updated_at'] = date('d-M-Y H:i:s', strtotime($pro->updated_at));
                            $tmp1['reviews'] = $this->customerReviews($pro->product_id, $pro->order_id);
                            $tmp1['product_url'] = (isset($pro->products)) ? $pro->products->product_url : '';

                            $order_history = OrderHistory::where('order_id', $info->id)->where('product_id', $pro->product_id)->where('order_id', $info->id)->where('order_status_id', 3)->first();
                            if ($order_history) {
                                $tmp1['cancelled_date'] = date('d-M-Y H:i:s', strtotime($order_history->created_at));
                            } else {
                                $order_history_cancel_request = OrderHistory::where('order_id', $info->id)->where('product_id', $pro->product_id)->where('order_id', $info->id)->where('order_status_id', 6)->first();
                                if ($order_history_cancel_request) {
                                    $tmp1['cancelled_date'] = date('d-M-Y H:i:s', strtotime($order_history_cancel_request->created_at));
                                }
                            }

                            $imagePath              = (isset($pro->products)) ? $pro->products->base_image : '';

                            if (!Storage::exists($imagePath)) {
                                $path               = asset('assets/logo/no-img-1.png');
                            } else {
                                $url                = Storage::url($imagePath);
                                $path               = asset($url);
                            }

                            $tmp1['image']                   = $path;
                            $tmp1['tracking'] = self::orderHistory($pro->product_id, $pro->order_id);
                            $tmp1['merchant_shipment'] = self::merchantShipment($pro->id, $pro->order_id);
                            $itemArray[] = $tmp1;
                        }
                    }
                }
                $tmp['items'] = $itemArray;

                $tmp['payment_data'] = $order_data->payments();
                #customers
                $tmp['customer'] = $order_data->customer;
                $tracking = [];
                if (isset($order_data->tracking) && !empty($order_data->tracking)) {
                    foreach ($order_data->tracking as $track) {
                        $tra = [];
                        $tra['id'] = $track->id;
                        $tra['action'] = $track->action;
                        $tra['description'] = $track->description;
                        $tra['order_id'] = $track->order_id;
                        $tra['description'] = $track->description;
                        $tra['created_at'] = date('d-M-Y H:i:s', strtotime($track->created_at));

                        $tracking[] = $tra;
                    }
                }
                //$tmp['tracking'] = $tracking;

                $orders[$i] = $tmp;
                $i++;
            }
        } else {
            $orderAll = Order::join('order_products', 'orders.id', '=', 'order_products.order_id')
                ->where('orders.customer_id', auth()->guard('api')->user()->id)
                ->whereNotIn('order_products.status', [1, 3, 6, 9])
                ->select([DB::RAW('DISTINCT(mm_orders.id)'), 'orders.*'])
                // ->select('orders.*', 'order_products.status AS order_item_status')
                ->orderBy('orders.id', $sort)
                ->get();
            foreach ($orderAll as $info) {
                $order_data = Order::find($info->id);
                $tmp['id'] = $order_data->id;
                $tmp['order_no'] = $order_data->order_no;
                $tmp['shipping_type'] = $order_data->shipping_type;
                $tmp['amount'] = $order_data->amount;
                $tmp['tax_percentage'] = $order_data->tax_percentage;
                $tmp['tax_amount'] = $order_data->tax_amount;
                $tmp['shipping_amount'] = $order_data->shipping_amount;
                $tmp['discount_amount'] = $order_data->discount_amount;
                $tmp['coupon_amount'] = $order_data->coupon_amount;
                $tmp['coupon_code'] = $order_data->coupon_code;
                $tmp['sub_total'] = $order_data->sub_total;
                $tmp['billing_name'] = $order_data->billing_name;
                $tmp['billing_email'] = $order_data->billing_email;
                $tmp['billing_mobile_no'] = $order_data->billing_mobile_no;
                $tmp['billing_address_line1'] = $order_data->billing_address_line1;
                $tmp['billing_address_line2'] = $order_data->billing_address_line2;
                $tmp['billing_landmark'] = $order_data->billing_landmark;
                $tmp['billing_country'] = $order_data->billing_country;
                $tmp['billing_post_code'] = $order_data->billing_post_code;
                $tmp['billing_state'] = $order_data->billing_state;
                $tmp['billing_city'] = $order_data->billing_city;

                $tmp['shipping_name'] = $order_data->shipping_name;
                $tmp['shipping_email'] = $order_data->shipping_email;
                $tmp['shipping_mobile_no'] = $order_data->shipping_mobile_no;
                $tmp['shipping_address_line1'] = $order_data->shipping_address_line1;
                $tmp['shipping_address_line2'] = $order_data->shipping_address_line2;
                $tmp['shipping_landmark'] = $order_data->shipping_landmark;
                $tmp['shipping_country'] = $order_data->shipping_country;
                $tmp['shipping_post_code'] = $order_data->shipping_post_code;
                $tmp['shipping_state'] = $order_data->shipping_state;
                $tmp['shipping_city'] = $order_data->shipping_city;
                $tmp['invoice_file'] = asset('storage/invoice_order/' . $order_data->order_no . '.pdf');
                $tmp['order_date'] = date('d-M-Y H:i:s', strtotime($order_data->created_at));
                $payments = Payment::where('order_id', $order_data->id)->first();
                $response = isset($payments) ? unserialize($payments->response) : '';
                $tmp['payment_mode'] = isset($response['payment_mode']) ? $response['payment_mode'] : '';
                $tmp['card_name'] = isset($response['card_name']) ? $response['card_name'] : '';
                $itemArray = [];
                $status_array = [];

                if (isset($order_data->orderItems) && !empty($order_data->orderItems)) {
                    foreach ($order_data->orderItems as $pro) {
                        if ($pro->status != 3 || $pro->status != 6 || $pro->status != 1 || $pro->status != 9 || $pro->status != 13) {
                            $tmp1 = [];
                            $tmp1['item_id'] = $pro->id;
                            $tmp1['product_id'] = $pro->product_id;
                            $tmp1['order_id'] = $pro->order_id;
                            $tmp1['order_no'] = $info->order_no;
                            $tmp1['product_name'] = $pro->product_name;
                            $tmp1['hsn_code'] = $pro->hsn_code;
                            $tmp1['sku'] = $pro->sku;
                            $tmp1['quantity'] = $pro->quantity;
                            $tmp1['price'] = $pro->price;
                            $tmp1['tax_amount'] = $pro->tax_amount;
                            $tmp1['tax_percentage'] = $pro->tax_percentage;
                            $tmp1['quantity'] = $pro->quantity;
                            $tmp1['sub_total'] = $pro->sub_total;
                            $tmp1['status'] = $pro->getStatus->status_name;
                            $status_array[]  = $pro->status;
                            $tmp1['status_id'] = $pro->status;
                            $tmp1['created_at'] = date('d-M-Y H:i:s', strtotime($pro->created_at));
                            $tmp1['updated_at'] = date('d-M-Y H:i:s', strtotime($pro->updated_at));
                            $tmp1['reviews'] = $this->customerReviews($pro->product_id, $pro->order_id);
                            $tmp1['product_url'] = (isset($pro->products)) ? $pro->products->product_url : '';

                            $order_history = OrderHistory::where('order_id', $info->id)->where('product_id', $pro->product_id)->where('order_id', $info->id)->where('order_status_id', 3)->first();
                            if ($order_history) {
                                $tmp1['cancelled_date'] = date('d-M-Y H:i:s', strtotime($order_history->created_at));
                            } else {
                                $order_history_cancel_request = OrderHistory::where('order_id', $info->id)->where('product_id', $pro->product_id)->where('order_id', $info->id)->where('order_status_id', 6)->first();
                                if ($order_history_cancel_request) {
                                    $tmp1['cancelled_date'] = date('d-M-Y H:i:s', strtotime($order_history_cancel_request->created_at));
                                }
                            }

                            $imagePath              = (isset($pro->products)) ? $pro->products->base_image : '';

                            if (!Storage::exists($imagePath)) {
                                $path               = asset('assets/logo/no-img-1.png');
                            } else {
                                $url                = Storage::url($imagePath);
                                $path               = asset($url);
                            }

                            $tmp1['image']                   = $path;
                            $tmp1['tracking'] = self::orderHistory($pro->product_id, $pro->order_id);
                            $tmp1['merchant_shipment'] = self::merchantShipment($pro->id, $pro->order_id);
                            $itemArray[] = $tmp1;
                        }
                    }
                }
                $tmp['items'] = $itemArray;
                $cancel_button_show = [1, 2, 8];

                $tmp['cancel_status'] = !empty(array_intersect($cancel_button_show, $status_array));
                $tmp['payment_data'] = $order_data->payments();
                #customers
                $tmp['customer'] = $order_data->customer;
                $tracking = [];
                if (isset($order_data->tracking) && !empty($order_data->tracking)) {
                    foreach ($order_data->tracking as $track) {
                        $tra = [];
                        $tra['id'] = $track->id;
                        $tra['action'] = $track->action;
                        $tra['description'] = $track->description;
                        $tra['order_id'] = $track->order_id;
                        $tra['description'] = $track->description;
                        $tra['created_at'] = date('d-M-Y H:i:s', strtotime($track->created_at));

                        $tracking[] = $tra;
                    }
                }
                //$tmp['tracking'] = $tracking;

                $orders[$i] = $tmp;
                $i++;
            }
        }
        return new Response(array('error' => 0, 'status_code' => 200, 'message' => 'Data loaded successfully', 'status' => 'success', 'data' => $orders), 200);
    }

    public function getOrderData(Request $request)
    {
        $order_no           = $request->order_no;
        $info           = Order::where('order_no', $order_no)->first();
        if (!isset($info) && empty($info)) {
            return new Response(array('error' => 1, 'status_code' => 400, 'message' => 'No data found', 'status' => 'failure', 'data' => []), 400);
        }
        $tmp = [];
        $tra = [];
        if (isset($info) && !empty($info)) {

            $tmp['id'] = $info->id;
            $tmp['order_no'] = $info->order_no;
            $tmp['shipping_name'] = $info->shipping_name;
            $tmp['shipping_email'] = $info->shipping_email;
            $tmp['shipping_mobile_no'] = $info->shipping_mobile_no;
            $tmp['email'] = $info->billing_email;
            $tmp['total_amount'] = $info->amount;
            $tmp['shipping_address_line1'] = $info->shipping_address_line1;
            $tmp['shipping_address_line2'] = $info->shipping_address_line2;
            $tmp['shipping_landmark'] = $info->shipping_landmark;
            $tmp['shipping_country'] = $info->shipping_country;
            $tmp['shipping_post_code'] = $info->shipping_post_code;
            $tmp['shipping_state'] = $info->shipping_state;
            $tmp['shipping_city'] = $info->shipping_city;
            $tmp['delivery_date'] = date("d-m-Y", strtotime("$info->created_at +7 days"));
            if (isset($info->payments) && !empty($info->payments)) {
                $tmp['payment_id'] = $info->payments->id;
                $tmp['payment_transaction_id'] = $info->payments->payment_no;
                $tmp['payment_status'] = $info->payments->status;
            }
        }
        return new Response(array('error' => 0, 'status_code' => 200, 'message' => 'Data loaded successfully', 'status' => 'success', 'data' => $tmp), 200);
    }

    public function getOrderByOrderNo(Request $request)
    {
        $customer_id        = $request->customer_id ?? 1;
        $order_no           = $request->order_no;
        $info           = Order::where('order_no', $order_no)->first();
        if (!isset($info) && empty($info)) {
            return new Response(array('error' => 1, 'status_code' => 400, 'message' => 'No data found', 'status' => 'failure', 'data' => []), 400);
        }
        $orders = [];
        if (isset($info) && !empty($info)) {

            $tmp['id'] = $info->id;
            $tmp['order_no'] = $info->order_no;
            $tmp['shipping_type'] = $info->shipping_type;
            $tmp['amount'] = $info->amount;
            $tmp['tax_percentage'] = $info->tax_percentage;
            $tmp['tax_amount'] = $info->tax_amount;
            $tmp['shipping_amount'] = $info->shipping_amount;
            $tmp['discount_amount'] = $info->discount_amount;
            $tmp['coupon_amount'] = $info->coupon_amount;
            $tmp['coupon_code'] = $info->coupon_code;
            $tmp['sub_total'] = $info->sub_total;
            $tmp['billing_name'] = $info->billing_name;
            $tmp['billing_email'] = $info->billing_email;
            $tmp['billing_mobile_no'] = $info->billing_mobile_no;
            $tmp['billing_address_line1'] = $info->billing_address_line1;
            $tmp['billing_address_line2'] = $info->billing_address_line2;
            $tmp['billing_landmark'] = $info->billing_landmark;
            $tmp['billing_country'] = $info->billing_country;
            $tmp['billing_post_code'] = $info->billing_post_code;
            $tmp['billing_state'] = $info->billing_state;
            $tmp['billing_city'] = $info->billing_city;

            $tmp['shipping_name'] = $info->shipping_name;
            $tmp['shipping_email'] = $info->shipping_email;
            $tmp['shipping_mobile_no'] = $info->shipping_mobile_no;
            $tmp['shipping_address_line1'] = $info->shipping_address_line1;
            $tmp['shipping_address_line2'] = $info->shipping_address_line2;
            $tmp['shipping_landmark'] = $info->shipping_landmark;
            $tmp['shipping_country'] = $info->shipping_country;
            $tmp['shipping_post_code'] = $info->shipping_post_code;
            $tmp['shipping_state'] = $info->shipping_state;
            $tmp['shipping_city'] = $info->shipping_city;


            $tmp['status'] = $info->status;
            $tmp['invoice_file'] = asset('storage/invoice_order/' . $info->order_no . '.pdf');
            $tmp['order_date'] = date('d-M-Y H:i:s', strtotime($info->created_at));

            $payments = Payment::where('order_id', $info->id)->first();
            $response = isset($payments) ? unserialize($payments->response) : '';
            $tmp['payment_mode'] = isset($response['payment_mode']) ? $response['payment_mode'] : '';
            $tmp['card_name'] = isset($response['card_name']) ? $response['card_name'] : '';

            $itemArray = [];
            if (isset($info->orderItems) && !empty($info->orderItems)) {
                $ordered_quantity = 0;
                foreach ($info->orderItems as $pro) {
                    $tmp1 = [];
                    $tmp1['item_id'] = $pro->id;
                    $tmp1['product_id'] = $pro->product_id;
                    $tmp1['order_id'] = $pro->order_id;
                    $tmp1['order_no'] = $info->order_no;
                    $tmp1['product_name'] = $pro->product_name;
                    $tmp1['hsn_code'] = $pro->hsn_code;
                    $tmp1['sku'] = $pro->sku;
                    $tmp1['quantity'] = $pro->quantity;
                    $ordered_quantity = $ordered_quantity + $pro->quantity;
                    $tmp1['price'] = $pro->price;
                    $tmp1['tax_amount'] = $pro->tax_amount;
                    $tmp1['tax_percentage'] = $pro->tax_percentage;
                    $tmp1['quantity'] = $pro->quantity;
                    $tmp1['sub_total'] = $pro->sub_total;
                    $tmp1['status'] = $pro->getStatus->status_name;
                    $tmp1['status_id'] = $pro->status;
                    $tmp1['created_at'] = date('d-M-Y H:i:s', strtotime($pro->created_at));
                    $tmp1['updated_at'] = date('d-M-Y H:i:s', strtotime($pro->updated_at));
                    $tmp1['reviews'] = $this->customerReviews($pro->product_id, $pro->order_id);

                    $imagePath              = (isset($pro->products)) ? $pro->products->base_image : '';

                    if (!Storage::exists($imagePath)) {
                        $path               = asset('assets/logo/no-img-1.png');
                    } else {
                        $url                = Storage::url($imagePath);
                        $path               = asset($url);
                    }

                    $tmp1['image']                   = $path;
                    $tmp1['tracking'] = self::orderHistory($pro->product_id, $pro->order_id);
                    $itemArray[] = $tmp1;
                }
            }
            $tmp['items'] = $itemArray;
            $tmp['total_quantity'] = $ordered_quantity;
            #customers
            $tmp['customer'] = $info->customer;
            $tracking = [];
            if (isset($info->tracking) && !empty($info->tracking)) {
                foreach ($info->tracking as $track) {
                    $tra = [];
                    $tra['id'] = $track->id;
                    $tra['action'] = $track->action;
                    $tra['description'] = $track->description;
                    $tra['order_id'] = $track->order_id;
                    $tra['description'] = $track->description;
                    $tra['created_at'] = date('d-M-Y H:i:s', strtotime($track->created_at));

                    $tracking[] = $tra;
                }
            }
            //$tmp['tracking'] = $tracking;

            $orders = $tmp;
        }
        return new Response(array('error' => 0, 'status_code' => 200, 'message' => 'Data loaded successfully', 'status' => 'success', 'data' => $orders), 200);
    }

    public function getCancelReason()
    {
        $reason = OrderCancelReason::select('id', 'name', 'order_by')->where('status', 'published')->get();
        return new Response(array('error' => 0, 'status_code' => 200, 'message' => 'Data loaded successfully', 'status' => 'success', 'data' => $reason), 200);
    }

    public function bulkCancelOrder(Request $request)
    {
        $order_no = $request->order_id;
        $cancel_reason      = $request->cancel_reason_id;
        $cancel_comment      = $request->cancel_comment;
        if (isset($order_no)) {
            $order_info = OrderProduct::select('orders.*', 'orders.status AS order_status', 'order_products.id AS item_id', 'order_products.*')
                ->join('orders', 'orders.id', '=', 'order_products.order_id')
                ->where('orders.order_no', $order_no)
                ->get();
            $status_array = [1, 2, 8];
            if (isset($order_info) && !empty($order_info)) {
                foreach ($order_info as $orderInfo) {
                    $order_id = $orderInfo->order_id;
                    $item_id = $orderInfo->item_id;
                    $order_item_product = OrderProduct::find($item_id);
                    $product_id = $order_item_product->product_id;

                    // $error = 1;
                    // $message = 'Cancel Request has been sent already, You will receive mail about your cancel orders';

                    // return new Response(array('error' => $error, 'status_code' => 200, 'message' => $message, 'status' => 'failure', 'data' => []), 200);
                    if ($order_item_product->status !== 6 && in_array($order_item_product->status, $status_array)) {
                        $order_status    = OrderStatus::where('status', 'published')->where('id', 6)->first();

                        $order = Order::find($order_id);
                        $order->status = 'cancel_requested';
                        $order->description = $cancel_comment;
                        $order->cancel_reason_id =  $cancel_reason;
                        $order->order_status_id = $order_status->id;
                        $order->save();

                        $orderProduct = OrderProduct::where('order_id', $order_id)->where('product_id', $product_id)->first();
                        $orderProduct->status = $order->order_status_id;
                        $orderProduct->save();
                        /** Merchant Order status change */

                        $merchant_order = MerchantOrder::where('order_id', $order_id)->where('order_product_id', $orderProduct->id)->first();
                        if ($merchant_order) {
                            $merchant_order->order_status = "cancel_requested";
                            $merchant_order->save();
                        }

                        /**** order history */

                        $his['order_id'] = $order_id;
                        $his['product_id'] = $product_id;
                        $his['action'] = $order_status->status_name ?? '';
                        $his['order_status_id'] = $order_status->id;
                        $his['description'] = $cancel_reason;
                        OrderHistory::create($his);

                        $cancelData = [
                            'order_id' => $order_id,
                            'product_id' => $product_id,
                            'customer_email' => $orderInfo['billing_email'],
                            'cancel_reason' => $cancel_comment,
                        ];

                        CancelOrder::dispatch($cancelData);
                    }
                }
            } else {
                $error = 1;
                $message = 'Order not found, Please contact admin';
                return new Response(array('error' => $error, 'status_code' => 200, 'message' => $message, 'status' => 'failure', 'data' => []), 200);
            }
            return new Response(array('error' => 0, 'status_code' => 200, 'message' => 'Cancel Request has been sent successfully, You will receive mail about your cancel orders', 'status' => 'success', 'data' => $order_info), 200);
        }
        return new Response(array('error' => 1, 'status_code' => 200, 'message' => 'Please try again', 'status' => 'falied', 'data' => []), 200);
    }

    public function requestCancelOrder(Request $request)
    {
        $customer_id        = auth()->guard('api')->user()->id;
        $product_id         = $request->product_id;
        $order_id           = $request->order_id;
        $cancel_reason      = $request->cancel_reason_id;
        $cancel_comment      = $request->cancel_comment;
        $item_id = $request->item_id;
        if (isset($order_id) && isset($product_id)) {
            /* $orderInfo = Order::select('orders.*', 'op.*')
                ->join('order_products AS op', 'orders.id', '=', 'op.order_id')
                ->where('orders.id', $order_id)
                ->andWhere('orders.customer_id', $customer_id)
                ->andWhere('op.product_id', $product_id)
                ->groupBy('orders.id')->first(); */

            $orderInfo = OrderProduct::select('orders.*', 'orders.status AS order_status', 'order_products.*')
                ->join('orders', 'orders.id', '=', 'order_products.order_id')
                ->where('orders.id', $order_id)
                ->where('order_products.product_id', $product_id)
                ->groupBy('order_products.id')->first();


            if (isset($orderInfo) && !empty($orderInfo)) {
                $order_item_product = OrderProduct::find($item_id);
                if ($order_item_product->status == 6) {
                    $error = 1;
                    $message = 'Cancel Request has been sent already, You will receive mail about your cancel orders';

                    return new Response(array('error' => $error, 'status_code' => 200, 'message' => $message, 'status' => 'failure', 'data' => []), 200);
                } else {
                    $order_status    = OrderStatus::where('status', 'published')->where('id', 6)->first();

                    $order = Order::find($order_id);
                    $order->status = 'cancel_requested';
                    $order->description = $cancel_comment;
                    $order->cancel_reason_id =  $cancel_reason;
                    $order->order_status_id = $order_status->id;
                    $order->save();

                    $orderProduct = OrderProduct::where('order_id', $order_id)->where('product_id', $product_id)->first();
                    $orderProduct->status = $order->order_status_id;
                    $orderProduct->save();
                    /** Merchant Order status change */

                    $merchant_order = MerchantOrder::where('order_id', $order_id)->where('order_product_id', $orderProduct->id)->first();
                    if ($merchant_order) {
                        $merchant_order->order_status = "cancel_requested";
                        $merchant_order->save();
                    }

                    /**** order history */

                    $his['order_id'] = $order_id;
                    $his['product_id'] = $product_id;
                    $his['action'] = $order_status->status_name ?? '';
                    $his['order_status_id'] = $order_status->id;
                    $his['description'] = $cancel_reason;
                    OrderHistory::create($his);

                    $cancelData = [
                        'order_id' => $order_id,
                        'product_id' => $product_id,
                        'customer_email' => $orderInfo['billing_email'],
                        'cancel_reason' => $cancel_comment,
                    ];

                    CancelOrder::dispatch($cancelData);
                }
            } else {
                $error = 1;
                $message = 'Order not found, Please contact admin';
                return new Response(array('error' => $error, 'status_code' => 200, 'message' => $message, 'status' => 'failure', 'data' => []), 200);
            }

            return new Response(array('error' => 0, 'status_code' => 200, 'message' => 'Cancel Request has been sent successfully, You will receive mail about your cancel orders', 'status' => 'success', 'data' => $orderInfo), 200);
        }
        return new Response(array('error' => 1, 'status_code' => 200, 'message' => 'Please try again', 'status' => 'falied', 'data' => []), 200);
    }


    public function requestExchangeOrder(Request $request)
    {

        $response = [
            'error' => 0,
            'status_code' => 200,
            'message' => '',
            'status' => 'success',
            'data' => [],
        ];
        $customer_id = auth()->guard('api')->user()->id;
        $item_id     = $request->item_id;
        $reason     = $request->reason;
        $reason_id  = $request->reason_id;

        $validator  = Validator::make($request->all(), [
            'item_id' => 'required|int',
            'reason_id' => 'required|int',
            'reason' => 'required|string',
        ]);
        if ($validator->passes()) {

            $orderProduct = OrderProduct::select('order_products.*', 'orders.customer_id')
                ->leftJoin('orders', 'orders.id', '=', 'order_products.order_id')
                ->where('orders.customer_id', $customer_id)
                ->where('order_products.id', $item_id)->first();

            if (isset($orderProduct) && !empty($orderProduct)) {
                $shipped_date = Carbon::parse($orderProduct->updated_at)->addDays(3);
                if ($orderProduct->status == 10) {
                    $response['error'] = 1;
                    $response['message'] = 'Exchange Request has been sent already, You will receive mail about your cancel orders';
                    $response['status'] = 'failure';
                } elseif ($orderProduct->status ==  5 && strtotime($shipped_date) >= strtotime(Carbon::now())) {
                    DB::beginTransaction();
                    $seller_id = ($orderProduct->assigned_seller_2 == null) ? $orderProduct->assigned_seller_1 : $orderProduct->assigned_seller_2;
                    try {
                        $order = Order::find($orderProduct->order_id);
                        $order->status = 'exchange_requested';
                        $order->order_status_id = 10;
                        $order->save();
                        if ($seller_id && $order->order_status_id == 10) {
                            $merchant_order = MerchantOrder::where('order_id', $order->id)->first(); // Use 'where' to find by a specific field
                            if ($merchant_order) {
                                $merchant_order->order_status = 'exchange_requested';
                                $merchant_order->save();
                            }
                        }
                        $orderProduct->status = 10;
                        $orderProduct->save();

                        /**** order history */
                        $orderStatus = OrderStatus::find(10);
                        if ($orderStatus) {
                            $action = $orderStatus->status_name;
                        }

                        OrderHistory::create([
                            'order_id' => $orderProduct->order_id,
                            'product_id' => $orderProduct->product_id,
                            'action' => $action ?? '',
                            'order_status_id' => 10,
                            'description' => $reason
                        ]);

                        OrderExchange::create([
                            'order_id' => $orderProduct->order_id,
                            'product_id' => $orderProduct->product_id,
                            'order_item_id' => $orderProduct->id,
                            'customer_id' => $customer_id,
                            'seller_id' => $seller_id,
                            'reason' => $reason,
                            'reason_id' => $reason_id,
                            'quantity' => $orderProduct->quantity,
                            'delivered_at' => $orderProduct->updated_at,
                            'status' => OrderExchange::EXCHANGE_STATUS[0],
                        ]);

                        $eventData = [
                            'item_id' => $orderProduct->id,
                            'order_id' => $orderProduct->order_id,
                            'product_id' => $orderProduct->product_id,
                            'customer_id' => $customer_id,
                            'seller_id' => $seller_id,
                            'customer_email' => $order['billing_email'],
                            'reason' => $reason,
                            'status' => 0
                        ];
                        ProductExchange::dispatch($eventData);
                        DB::commit();
                    } catch (\Exception $e) {
                        DB::rollback();
                        throw $e;
                    } catch (\Throwable $e) {
                        DB::rollback();
                        throw $e;
                    }

                    $response['error'] = 0;
                    $response['message'] = 'Exchange Request Submitted Successfully! Check Your Email for Updates';
                } else {
                    $response['error'] = 1;
                    $response['message'] = 'Exchange cannot be accepted after 3 days of delivery';
                    $response['status'] = 'failure';
                }
            } else {
                $response['error'] = 1;
                $response['message'] = 'Order not found, Please contact admin';
                $response['status'] = 'failure';
            }
        } else {
            $response['error'] = 1;
            $response['message'] = 'Invalid datas';
            $response['status'] = 'failure';
            $response['data'] = $validator->errors();
        }
        return new Response($response, 200);
    }

    public function trackShipment(Request $request, ShipRocketService $shipRocketService)
    {
        $shipment_id = $request->shipment_id;
        return  $shipRocketRsponse = $shipRocketService->trackShipment($shipment_id);
    }

    public function customerReviews($product_id, $order_id)
    {
        $datas = Reviews::select('reviews.*', 'customers.email', 'customers.mobile_no')
            ->leftJoin('products', 'products.id', '=', 'reviews.product_id')
            ->leftJoin('customers', 'customers.id', '=', 'reviews.customer_id')
            ->where('product_id', $product_id)
            ->where('order_id', $order_id)
            ->where('customer_id', auth()->guard('api')->user()->id)->get();
        return $datas;
    }

    protected static function orderHistory($product_id, $order_id)
    {
        $track_data = [];
        $exchange_status_id = $cancel_status_id = '';
        $cancel_status = [3, 6];
        $order_status_show_in_front = OrderStatus::where('show_in_front', 1)->orderBy('order', 'asc')->get()->pluck('id')->toArray();
        $exchange_status = [10, 11, 12, 13];
        $order_product = OrderProduct::where('order_id', $order_id)->where('product_id', $product_id)->first();
        $datas = OrderHistory::join('order_statuses', 'order_statuses.id', '=', 'order_histories.order_status_id')->where('show_in_front', 1)->where('product_id', $product_id)->where('order_id', $order_id)->orderBy('order', 'asc')->get();
        $exchange_info = $datas->pluck('order_status_id')->toArray();
        foreach ($cancel_status as $cancel_state) {
            if (!in_array($cancel_state, $exchange_info)) {
                $order_status_show_in_front = array_diff($order_status_show_in_front, [$cancel_state]);
            }
        }

        $cancel_status_exists = array_intersect($cancel_status, $exchange_info);
        if (!empty($cancel_status_exists)) {
            $cancel_status_id = end($cancel_status_exists);
        }
        $exchange_status_exists = array_intersect($exchange_status, $exchange_info);
        if (!empty($exchange_status_exists)) {
            $exchange_status_id = end($exchange_status_exists);
        }

        $track_order_status = array_diff($order_status_show_in_front, $exchange_status);
        array_push($track_order_status, $exchange_status_id);
        array_push($track_order_status, $cancel_status_id);

        $track_order_data = OrderStatus::whereIn('id', $track_order_status)->orderBy('order', 'asc')->get();
        // $track_order_data = OrderHistory::select('order_statuses.id','order_statuses.status_name', 'order_statuses.image', 'order_histories.created_at', 'order_histories.updated_at')->join('order_statuses','order_statuses.id','=','order_histories.order_status_id')->where('product_id', $product_id)->where('order_id', $order_id)->whereIn('order_statuses.id', $track_order_status)->orderBy('order', 'asc')->get();
        foreach ($track_order_data as $track_order_info) {
            // echo $track_order_info->id;
            $order_history = OrderHistory::where('order_id', $order_id)->where('product_id', $product_id)->where('order_id', $order_id)->where('order_status_id', $track_order_info->id)->first();
            if ($order_product) {
                if ($order_product->status == $track_order_info->id) {
                    $active = true;
                } else {
                    if ($order_history) {
                        $active = true;
                    } else {
                        $active = false;
                    }
                }
            }
            $track_data[] = [
                'status_id' => $track_order_info->id,
                'status_name' => $track_order_info->status_name,
                'status_image' => $track_order_info->image,
                'active' => $active,
                'created_at' => $order_history->created_at ?? '',
                'updated_at' => $order_history->updated_at ?? '',
            ];
        }
        return $track_data   ?? [];
    }

    public static function merchantShipment($product_id, $order_id)
    {
        $shipment_data = OrderProduct::find($product_id);
        $ship_data = [
            'shipment_tracking_code' => $shipment_data->shipment_tracking_code ?? '',
            'shipment_tracking_message' => $shipment_data->shipment_tracking_message ?? ''
        ];
        return $ship_data   ?? [];
    }
}
