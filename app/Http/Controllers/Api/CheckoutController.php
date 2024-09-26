<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\OrderMail;
use App\Mail\DynamicMail;
use App\Models\Cart;
use App\Models\CartAddress;
use App\Models\Master\State;
use App\Models\CartShiprocketResponse;
use App\Models\GlobalSettings;
use App\Models\Master\CustomerAddress;
use App\Models\Master\EmailTemplate;
use App\Models\Master\OrderStatus;
use App\Models\MerchantOrder;
use App\Models\MerchantProduct;
use App\Models\MerchantsOrder;
use App\Models\Order;
use App\Models\OrderHistory;
use App\Models\OrderProduct;
use App\Models\Payment;
use App\Models\Product\Product;
use App\Models\Seller\Merchant;
use App\Models\ShippingCharge;
use App\Models\Zone;
use App\Models\ZoneState;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Softon\Indipay\Facades\Indipay;
use Razorpay\Api\Api;
use PDF;
use Mail;

class CheckoutController extends Controller
{

    public function startPayment(Request $request,)
    {
        if ($request->order_id) {
            $order_id    = base64_decode($request->order_id);

            $order_info = Order::find($order_id);
            if ($order_info) {
                $orderData = [
                    'tid' => date('ymdhis'),
                    'order_id' => $order_id,
                    'amount' => $order_info->amount,
                    'billing_name' => $order_info->billing_name,
                    'billing_address' => $order_info->billing_address_line1,
                    'billing_city' => $order_info->billing_city,
                    'billing_state' => $order_info->billing_state,
                    'billing_zip' => $order_info->billing_post_code,
                    'billing_country' => $order_info->billing_country,
                    'billing_tel' => $order_info->billing_mobile_no,
                    'billing_email' => $order_info->billing_email,
                    'delivery_name' => $order_info->shipping_name,
                    'delivery_address' => $order_info->shipping_address_line1,
                    'delivery_city' => $order_info->shipping_city,
                    'delivery_state' => $order_info->shipping_state,
                    'delivery_zip' => $order_info->shipping_post_code,
                    'delivery_country' => $order_info->shipping_country,
                    'delivery_tel' => $order_info->shipping_mobile_no

                ];
                $indipayOrder = Indipay::prepare($orderData);

                return Indipay::gateway('CCAvenue ')->process($indipayOrder);
            } else {

                return response()->json(array('error' => 1, 'status_code' => 400, 'message' => 'UnAuthorized Access', 'status' => 'failure', 'data' => []), 400);
            }
        } else {

            return response()->json(array('error' => 1, 'status_code' => 400, 'message' => 'Payment link expired', 'status' => 'failure', 'data' => []), 400);
        }
    }

    public function ccavResponseHandler(Request $request)
    {

        if ($request) {
            $pay_response = $request->order_response;
            $paid_amount = $pay_response['amount'];
            $order_no = $pay_response['order_id'];
            $order_status = $pay_response['order_status'];
            $order_info = Order::where('order_no', $order_no)->first();

            // $encrypted_order_no = base64_encode($order_info->order_no);

            if (strtolower($order_status) == 'success' && $order_info->amount == $paid_amount) {
                Cart::where('customer_id', $order_info->customer_id)->delete();
                /**
                 *  1. do quantity update in product
                 *  2. update order status and payment response
                 *  3. insert in payment entry
                 */
                if ($order_info) {
                    $order_status    = OrderStatus::where('status', 'published')->where('order', 2)->first();
                    $order_info->status = 'placed';
                    $order_info->order_status_id = $order_status->id;

                    $order_info->save();

                    $order_items = OrderProduct::where('order_id', $order_info->id)->get();

                    if (isset($order_items) && !empty($order_items)) {
                        foreach ($order_items as $product) {
                            $product_info = Product::find($product->product_id);
                            $pquantity = $product_info->quantity - $product->quantity;
                            $product_info->quantity = $pquantity;
                            if ($pquantity == 0) {
                                $product_info->stock_status = 'out_of_stock';
                            }
                            $product_info->save();
                            $product->status = 2;
                            $product->update();
                            $orderStatus = OrderStatus::find(2);
                            if ($orderStatus) {
                                $action = $orderStatus->status_name;
                            }
                            $his['order_id'] = $order_info->id;
                            $his['product_id'] = $product->product_id;
                            $his['action'] = $action ?? '';
                            $his['description'] = 'Order has been placed successfully';
                            $his['order_status_id'] = 2;
                            OrderHistory::create($his);
                        }
                    }

                    $pay_ins['order_id'] = $order_info->id;
                    $pay_ins['payment_no'] = $pay_response['tracking_id'] ?? '';
                    $pay_ins['amount'] = $order_info->amount;
                    $pay_ins['paid_amount'] = $order_info->amount;
                    $pay_ins['payment_type'] = 'CCAvenue';
                    $pay_ins['payment_mode'] = $pay_response['payment_mode'] ?? 'online';
                    $pay_ins['response'] = serialize($pay_response);
                    $pay_ins['status'] = $pay_response['order_status']; //$finalorder['status'];

                    Payment::create($pay_ins);

                    /**** order history */
                    // $his['order_id'] = $order_info->id;
                    // $his['action'] = 'Order Placed';
                    // $his['description'] = 'Order has been placed successfully';
                    // OrderHistory::create($his);

                    /****
                     * 1.send email for order placed
                     * 2.send sms for notification
                     */
                    #generate invoice
                    $globalInfo = GlobalSettings::first();

                    $pdf = PDF::loadView('platform.invoice.index', compact('order_info', 'globalInfo'));
                    Storage::put('public/invoice_order/' . $order_info->order_no . '.pdf', $pdf->output());
                    #send mail
                    $emailTemplate = EmailTemplate::select('email_templates.*')
                        ->join('sub_categories', 'sub_categories.id', '=', 'email_templates.type_id')
                        ->where('sub_categories.slug', 'new-order')->first();

                    $globalInfo = GlobalSettings::first();

                    $extract = array(
                        'name' => $order_info->billing_name,
                        'regards' => $globalInfo->site_name,
                        'company_website' => '',
                        'company_mobile_no' => $globalInfo->site_mobile_no,
                        'company_address' => $globalInfo->address,
                        'dynamic_content' => '',
                        'order_id' => $order_info->order_no
                    );
                    $templateMessage = $emailTemplate->message;
                    $templateMessage = str_replace("{", "", addslashes($templateMessage));
                    $templateMessage = str_replace("}", "", $templateMessage);
                    extract($extract);
                    eval("\$templateMessage = \"$templateMessage\";");

                    $title = $emailTemplate->title;
                    $title = str_replace("{", "", addslashes($title));
                    $title = str_replace("}", "", $title);
                    eval("\$title = \"$title\";");

                    $filePath = 'storage/invoice_order/' . $order_info->order_no . '.pdf';
                    $send_mail = new OrderMail($templateMessage, $title, $filePath);
                    // return $send_mail->render();
                    sendEmailWithBcc($order_info->billing_email, $send_mail);

                    $adminEmailTemplate = EmailTemplate::select('email_templates.*')
                        ->join('sub_categories', 'sub_categories.id', '=', 'email_templates.type_id')
                        ->where('sub_categories.slug', 'new-order-admin')->first();
                    $extract = array(
                        'customer_name' => $order_info->billing_name,
                        'regards' => $globalInfo->site_name,
                        'company_website' => '',
                        'company_mobile_no' => $globalInfo->site_mobile_no,
                        'company_address' => $globalInfo->address,
                        'dynamic_content' => '',
                        'order_id' => $order_info->order_no
                    );
                    // print_r($adminEmailTemplate);
                    // die();
                    $templateMessage = $adminEmailTemplate->message;
                    $templateMessage = str_replace("{", "", addslashes($templateMessage));
                    $templateMessage = str_replace("}", "", $templateMessage);
                    extract($extract);
                    eval("\$templateMessage = \"$templateMessage\";");

                    $title = $adminEmailTemplate->title;
                    $title = str_replace("{", "", addslashes($title));
                    $title = str_replace("}", "", $title);
                    eval("\$title = \"$title\";");

                    // $filePath = 'storage/invoice_order/' . $order_info->order_no . '.pdf';
                    // $send_mail = new OrderMail($templateMessage, $title, $filePath);
                    // return $send_mail->render();
                    $send_mail = new DynamicMail($templateMessage, $adminEmailTemplate->title);
                    sendEmailWithBcc(env('MAIL_FROM_FOR_ADMIN'), $send_mail);
                    // Mail::to('abhinav@iktaraa.com')->send($send_mail);

                    #send sms for notification
                    $sms_params = array(
                        'name' => $order_info->billing_name,
                        'order_no' => $order_info->order_no,
                        'amount' => $order_info->amount,
                        'payment_through' => 'CCAvenue',
                        'mobile_no' => [$order_info->billing_mobile_no]
                    );
                    sendMuseeSms('new_order', $sms_params);

                    #send sms for notification
                    $sms_params = array(
                        'company_name' => env('APP_NAME'),
                        'order_no' => $order_info->order_no,
                        'reference_no' => '',
                        'company_address' => $globalInfo->address,
                        'billing_name' => $order_info->billing_name,
                        'mobile_no' => [$order_info->billing_mobile_no]
                    );
                    sendMuseeSms('confirm_order', $sms_params);
                    $assign_order_to_merchant = $this->autoAssignOrder($order_info);
                }
            } else {
                // if (isset($request->razor_response['error']) && !empty($request->razor_response['error'])) {

                //     $orderdata = $request->razor_response['error']['metadata'];
                //     $razorpay_payment_id = $orderdata['payment_id'];
                //     $razorpay_order_id = $orderdata['order_id'];

                //     $api = new Api($keyId, $keySecret);

                //     $finalorder = $api->order->fetch($orderdata['order_id']);

                // $order_info = Order::where('payment_response_id', $razorpay_order_id)->first();

                if ($order_info) {

                    $order_status    = OrderStatus::where('status', 'published')->where('id', 14)->first();

                    $order_info->status = 'payment_pending';
                    $order_info->order_status_id = $order_status->id;

                    $order_info->save();

                    $order_items = OrderProduct::where('order_id', $order_info->id)->get();

                    if (isset($order_items) && !empty($order_items)) {
                        foreach ($order_items as $product) {
                            $product_info = Product::find($product->id);
                            $pquantity = $product_info->quantity - $product->quantity;
                            $product_info->quantity = $pquantity;
                            if ($pquantity == 0) {
                                $product_info->stock_status = 'out_of_stock';
                            }
                            $product_info->save();
                        }
                    }

                    $pay_ins['order_id'] = $order_info->id;
                    $pay_ins['payment_no'] = $pay_response['tracking_id'] ?? '';
                    $pay_ins['amount'] = $order_info->amount;
                    $pay_ins['paid_amount'] = $order_info->amount;
                    $pay_ins['payment_type'] = 'CCAvenue';
                    $pay_ins['payment_mode'] = $pay_response['payment_mode'] ?? 'online';
                    $pay_ins['response'] = serialize($pay_response);
                    $pay_ins['status'] = $pay_response['order_status']; //$finalorder['status'];
                    // $pay_ins['enc_request'] = $encrypted_data;
                    // $pay_ins['enc_response'] = $pay_response;
                    // $pay_ins['status'] = 'paid';
                    // $payment_status = 'paid';
                    // $orders['payment_no'] = $payment_info->payment_no;

                    Payment::create($pay_ins);

                    // /**** order history */
                    // $his['order_id'] = $order_info->id;
                    // $his['action'] = 'Order Placed';
                    // $his['description'] = 'Order has been placed successfully';
                    // OrderHistory::create($his);
                    return response()->json(array('error' => 1, 'status_code' => 400, 'message' => $pay_response['status_message'], 'status' => 'failure', 'data' => []), 400);
                }
            }
        } else {
            return response()->json(array('error' => 1, 'status_code' => 400, 'message' => 'Payment Failed', 'status' => 'failure', 'data' => []), 400);
        }
        return response()->json(array('error' => 0, 'status_code' => 200, 'message' => 'Payment success', 'status' => 'success', 'success' => []), 200);
    }

    public function verifyCCavenueTransaction(Request $request)
    {

        $pay_response = $request->order_response;
        $orders = [];
        // if ($token) {
        // $order_no = base64_decode($token);

        $order_info = Order::where('order_no', $pay_response['order_id'])->first();

        // $pay_response = unserialize($order_info->payments->response);

        if ($order_info) {
            $orders = array(
                'order_no' => $order_info->order_no,
                'amount' => $order_info->amount
            );
            /**
             *  check status api
             */
            $merchant_json_data = array(
                'order_no' => $order_info->order_no,
                'reference_no' => $pay_response->tracking_id ?? ''
            );
            $access_code = env('INDIPAY_ACCESS_CODE');
            $working_key = env('INDIPAY_WORKING_KEY');
            $merchant_data = json_encode($merchant_json_data);
            $encrypted_data = $this->statusEncrypt($merchant_data, $working_key);

            $curl = curl_init();

            curl_setopt_array($curl, array(
                CURLOPT_URL => 'https://secure.ccavenue.com/apis/servlet/DoWebTrans?enc_request=' . $encrypted_data . '&access_code=AVRD71KE07CJ75DRJC&request_type=JSON&response_type=JSON&command=orderStatusTracker&version=1.2',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'POST',
            ));

            $response = curl_exec($curl);
            curl_close($curl);

            $status_response = '';
            $payment_status = '';
            if ($response) {

                $payment_info = Payment::where('order_id', $order_info->id)->orderBy('id', 'desc')->first();
                $payment_info->enc_request = $encrypted_data;
                $payment_info->enc_response = $response;
                $payment_info->status = 'paid';
                $payment_status = 'paid';
                $orders['payment_no'] = $payment_info->payment_no;
                /**
                 * insert in payment enc_response
                 */
                $information = explode('&', $response);

                $dataSize = sizeof($information);
                for ($i = 0; $i < $dataSize; $i++) {
                    $info_value = explode('=', $information[$i]);
                    if ($info_value[0] == 'enc_response') {
                        $status_response = $this->statusDecrypt(trim($info_value[1]), $working_key);
                        break;
                    }
                }

                $payment_info->enc_response_decrypted = $status_response;
                $obj = json_decode($status_response);
                $payment_info->save();
            }

            // 'enc_request=' . $encrypted_data . '&access_code=' . $access_code . '&command=orderStatusTracker&request_type=JSON&response_type=JSON';

            if (strtolower($payment_status) == 'paid' && $order_info->amount == $order_info->response_amount) {

                return response()->json(array('error' => 0, 'status_code' => 200, 'message' => 'Payment success', 'status' => 'success', 'data' => $orders), 200);
            } else {

                return response()->json(array('error' => 1, 'status_code' => 400, 'message' => 'Payment failed', 'status' => 'failure', 'data' => []), 400);
            }
        } else {

            return response()->json(array('error' => 1, 'status_code' => 400, 'message' => 'Payment token failed', 'status' => 'failure', 'data' => []), 400);
        }
        // } else {
        //     return response()->json(array('error' => 1, 'status_code' => 400, 'message' => 'Payment token failed', 'status' => 'failure', 'data' => []), 400);
        // }

    }

    public function proceedCcvCheckout(Request $request)
    {

        /***
         * Check order product is out of stock before proceed, if yes remove from cart and notify user
         * 1.insert in order table with status init
         * 2.INSERT IN Order Products
         */
        $order_status           = OrderStatus::where('status', 'published')->where('order', 1)->first();
        if (Auth()->guard('api')->check()) {
            $customer_id = auth()->guard('api')->user()->id;
        }

        $cart_total             = $request->cart_total;
        $cart_items             = $request->cart_items;
        $shipping_address       = $request->shipping_address;
        $billing_address        = $request->billing_address;
        $selected_shipping_fees = $request->selected_shipping_fees ?? '';
        if (isset($billing_address['stateid'])) {
            $state_info = State::find($billing_address['stateid']);
            $ins['state'] = $billing_address['state']  = $state_info->state_name;
            $ins['stateid'] = $state_info->id;
        }
        if (isset($shipping_address['stateid'])) {
            $state_info = State::find($shipping_address['stateid']);
            $shipping_address['state']  = $state_info->state_name;
        }
        if (isset($billing_address['id']) && ($billing_address['id'] == 0)) {
            // if ($billing_address['stateid']) {
            //     $state_info = State::find($billing_address['stateid']);
            //     $ins['state'] = $state_info->state_name;
            //     $ins['stateid'] = $state_info->id;
            // }
            // dd( $request->all() );
            // $details = $service->getShippingRocketOrderDimensions($request->customer_id);
            // echo 'duraira';die;
            $from_address_type = $billing_address['from_address_type'];
            $cart_info = Cart::where('customer_id', $customer_id)->first();

            $ins['customer_id'] = $customer_id;
            $ins['address_type_id'] = isset($billing_address['address_type_id']) ? $billing_address['address_type_id'] : '';
            $ins['first_name'] = isset($billing_address['first_name']) ? $billing_address['first_name'] : '';
            $ins['last_name'] = isset($billing_address['last_name']) ? $billing_address['last_name'] : '';
            $ins['email'] = isset($billing_address['email']) ? $billing_address['email'] : '';
            $ins['mobile_no'] = isset($billing_address['mobile_no']) ? $billing_address['mobile_no'] : '';
            $ins['address_line1'] = isset($billing_address['address_line1']) ? $billing_address['address_line1'] : '';
            $ins['country'] = 'india';
            $ins['post_code'] = isset($billing_address['post_code']) ? $billing_address['post_code'] : '';
            $ins['city'] = isset($billing_address['city']) ? $billing_address['city'] : '';
            $address_info = CustomerAddress::create($ins);

            $address = CustomerAddress::where('customer_id', $customer_id)->get();
            if (isset($cart_info) && !empty($cart_info)) {
                CartAddress::where('customer_id', $customer_id)
                    ->where('address_type', $from_address_type)->delete();
                $ins_cart = [];
                $ins_cart['cart_token'] = $cart_info->guest_token;
                $ins_cart['customer_id'] = $customer_id;
                $ins_cart['address_type'] = $from_address_type;
                $ins_cart['first_name'] = isset($billing_address['first_name']) ? $billing_address['first_name'] : '';
                $ins_cart['last_name'] = isset($billing_address['last_name']) ? $billing_address['last_name'] : '';
                $ins_cart['email'] = $billing_address['email'];
                $ins_cart['mobile_no'] = $billing_address['mobile_no'];
                $ins_cart['address_line1'] = $billing_address['address_line1'];
                $ins_cart['country'] = 'india';
                $ins_cart['post_code'] = $billing_address['post_code'];
                $ins_cart['state'] = $ins['state'];
                $ins_cart['city'] = $billing_address['city'];

                CartAddress::create($ins_cart);
            }
        }
        #check product is out of stock
        $errors                 = [];
        // if (!$shipping_address) {
        //     $message     = 'Shipping address not selected';
        //     $error = 1;
        //     $status = 'failure';
        //     $status_code = 400;
        // }

        if (isset($cart_items) && !empty($cart_items)) {
            foreach ($cart_items as $item) {
                $product_id     = $item['id'];
                $cart_id        = $item['cart_id'];
                $product_info   = Product::find($product_id);
                if ($product_info->quantity < $item['quantity']) {

                    $message     = $item['product_name'] . ' is out of stock, Product will be removed from cart.Please choose another';
                    $error = 1;
                    $status = 'failure';
                    $status_code = 400;
                    return response()->json(array('error' => $error, 'status_code' => $status_code, 'message' => $message, 'status' => $status, 'data' => []), $status_code);
                }
            }
        }
        /***
         * 1. get Shipping address
         * 2. get Billing Address
         * */
        // $shipppingAddressInfo = CustomerAddress::find($shipping_address);
        // $billingAddressInfo = CustomerAddress::find($billing_address);
        // dd( $selected_shipping_fees );
        $shippingCharges = [];
        $shipping_fee_id = $selected_shipping_fees['shipping_id'] ?? '';

        if (isset($cart_id) && isset($selected_shipping_fees) && !empty($selected_shipping_fees) && ($selected_shipping_fees['shipping_type'] != 'fees' && $selected_shipping_fees['shipping_type'] != 'flat')) {
            $cartInfo = Cart::find($cart_id);
            $cart_token = $cartInfo->guest_token;
            $shipmentResponse = CartShiprocketResponse::where('cart_token', $cart_token)->first();
            if (isset($shipmentResponse->shipping_charge_response_data) && !empty($shipmentResponse->shipping_charge_response_data)) {
                $shipChargeResponse = json_decode($shipmentResponse->shipping_charge_response_data);
                foreach ($shipChargeResponse->data->available_courier_companies as $items) {
                    if ($items->id == $shipping_fee_id) {
                        $shippingCharges = $items;
                    }
                }
            }
        }

        if (!empty($errors)) {


            $message = implode(',', $errors);
            $error = 1;
            $status = 'failure';
            $status_code = 400;
            return response()->json(array('error' => $error, 'status_code' => $status_code, 'message' => $message, 'status' => $status, 'data' => $data), $status_code);
        }

        $shipping_amount        = 0;
        $discount_amount        = 0;
        // $coupon_amount          = 0;
        $pay_amount             = filter_var($request->cart_total['total'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);

        $flat_type = [];
        if (isset($selected_shipping_fees) && !empty($selected_shipping_fees) && $selected_shipping_fees['shipping_type'] == 'flat') {
            $flat_type = $selected_shipping_fees;
            $shipping_amount = $selected_shipping_fees['shipping_charge_order'];
        } else {

            $shipping_type_info = ShippingCharge::find($shipping_fee_id);

            if (!$shipping_type_info) {
                /**
                 * check shiprocket data is available
                 */
                $shipping_amount = $cart_total['shipping_charge'];
            }
        }
        //         Log::debug($cart_total);
        // Log::debug($cart_total['coupon_amount']);
        $order_ins['customer_id'] = isset($customer_id) ? $customer_id : null;
        $order_ins['order_no'] = getOrderNo();
        $order_ins['shipping_options'] = $shipping_fee_id;
        $order_ins['shipping_type'] = $shippingCharges->courier_name ?? $shipping_type_info->shipping_title ?? $flat_type['shipping_type'] ?? 'Free';
        $order_ins['amount'] = $pay_amount;
        $order_ins['tax_amount'] = str_replace(',', '', $cart_total['tax_total']);
        $order_ins['tax_percentage'] = $cart_total['tax_percentage'];
        $order_ins['shipping_amount'] = $shipping_type_info->charges ?? $shipping_amount;
        $order_ins['discount_amount'] = $discount_amount;
        $order_ins['coupon_amount'] = $cart_total['coupon_amount'] ?? 0;
        $order_ins['coupon_code'] = $cart_total['coupone_code'] ?? '';
        if (isset($cart_total['coupone_code']) && $cart_total['coupone_code']) {
            $order_ins['is_coupon'] = 1;
        }
        $order_ins['sub_total'] = str_replace(',', '', $cart_total['product_tax_exclusive_total']);
        $order_ins['description'] = '';
        $order_ins['order_status_id'] = $order_status->id;
        $order_ins['status'] = 'pending';
        // $order_ins['is_guest'] = ($billing_address['address_type'] == 0) ? 1 : 0;
        $order_ins['billing_name'] = isset($billing_address['first_name']) ? $billing_address['first_name'] : '';
        $order_ins['billing_email'] = isset($billing_address['email']) ? $billing_address['email'] : '';
        $order_ins['billing_mobile_no'] = isset($billing_address['mobile_no']) ? $billing_address['mobile_no'] : '';
        $order_ins['billing_address_line1'] = isset($billing_address['address_line1']) ? $billing_address['address_line1'] : '';
        $order_ins['billing_address_line2'] = isset($billing_address['address_line2']) ? $billing_address['address_line2'] : null;
        $order_ins['billing_landmark'] = isset($billing_address['landmark']) ? $billing_address['landmark'] : null;
        $order_ins['billing_country'] = isset($billing_address['country']) ? $billing_address['country'] : null;
        $order_ins['billing_post_code'] = isset($billing_address['post_code']) ? $billing_address['post_code'] : null;
        $order_ins['billing_state'] = isset($billing_address['state']) ? $billing_address['state'] : null;
        $order_ins['billing_city'] = isset($billing_address['city']) ? $billing_address['city'] : null;

        $order_ins['shipping_name'] = $shipping_address['first_name'];
        $order_ins['shipping_email'] = $shipping_address['email'];
        $order_ins['shipping_mobile_no'] = $shipping_address['mobile_no'];
        $order_ins['shipping_address_line1'] = $shipping_address['address_line1'];
        $order_ins['shipping_address_line2'] = $shipping_address['address_line2'] ?? null;
        $order_ins['shipping_landmark'] = $shipping_address['landmark'] ?? null;
        $order_ins['shipping_country'] = $shipping_address['country'] ?? null;
        $order_ins['shipping_post_code'] = $shipping_address['post_code'];
        $order_ins['shipping_state'] = $shipping_address['state'] ?? null;
        $order_ins['shipping_city'] = $shipping_address['city'] ?? null;
        $order_ins['rocket_charge_response'] = json_encode($shippingCharges);
        $order_ins['rocket_charge_name'] = $shippingCharges->courier_name ?? null;
        $order_info = Order::create($order_ins);
        $order_id = $order_info->id;
        if (isset($cart_items) && !empty($cart_items)) {
            foreach ($cart_items as $item) {
                $items_ins['order_id'] = $order_id;
                $items_ins['product_id'] = $item['id'];
                $items_ins['product_name'] = $item['product_name'];
                $items_ins['hsn_code'] = $item['hsn_code'];
                $items_ins['mrp'] = $item['mrp_price'];
                $items_ins['sku'] = $item['sku'];
                $items_ins['quantity'] = $item['quantity'];
                $items_ins['price'] = $item['price'];
                $items_ins['tax_amount'] = $item['tax']['gstAmount'] ?? 0;
                $items_ins['tax_percentage'] = $item['tax_percentage'] ?? 0;
                $items_ins['sub_total'] = $item['sub_total'];

                OrderProduct::create($items_ins);
            }
        }

        $data['order_info'] = $order_info;
        return response()->json(array('error' => 0, 'status_code' => 200, 'message' => 'Order has been initiated successfully', 'status' => 'success', 'data' => $data), 200);
    }

    /**
     * check any merchant find in customer zone based on priority choose one merchant and assign
     * if no merchant found on customer zone find all the merchants who has the product and asort the merchant array by zone_order, find the nearest zone and assign the order
     * if no merchant has the ordered product notify admin
     */
    public function autoAssignOrder($order_info)
    // public function autoAssignOrder(Request $request)
    {
        $customer_state = $order_info->shipping_state;
        $customer_state_by_name = State::where('state_name', $customer_state)->first();
        $customer_state_id = $customer_state_by_name->id;
        $customer_zone = getZoneByStateId($customer_state_id);
        $customer_zone_id = $customer_zone['id'];
        $customer_zone_order = $customer_zone['order'];
        $order_items = OrderProduct::where('order_id', $order_info->id)->get();
        $to_email_address_admin = env('MAIL_FROM_FOR_ADMIN');
        $globalInfo = GlobalSettings::first();
        if (isset($order_items) && !empty($order_items)) {
            foreach ($order_items as $product) {
                $productData = OrderProduct::find($product->id);
                $masterProduct = Product::find($product->product_id);
                $merchant_item_order = MerchantProduct::join('merchants', 'merchants.id', '=', 'merchant_products.merchant_id')->where('merchants.status', 'approved')->where('merchants.mode', 'active')->where('product_id', $product->product_id)->where('qty', '>=', $product->quantity)->where('merchant_products.status', 'published')->where('zone_id', $customer_zone_id)->orderBy(DB::raw('ISNULL(priority), priority'), 'ASC')->first();
                if ($merchant_item_order) {
                    $merchant_id = $merchant_item_order->merchant_id;
                    $ins['order_id'] = $order_info->id;
                    $ins['merchant_id'] = $merchant_id;
                    $ins['order_product_id'] = $product->id;
                    $ins['qty'] = $product->quantity;
                    $ins['seller_price'] = $masterProduct->seller_price ?? NULL;
                    $ins['total'] = $masterProduct->seller_price ? ($masterProduct->seller_price * $product->quantity) : NULL;
                    $profit_margin_percentage = Merchant::getProfitMarginPercentage($product->product_id, $merchant_id, null);
                    $ins['merchant_profit_margin'] = $profit_margin_percentage;
                    $merchant_order = MerchantOrder::create($ins);
                    $email_slug_merchant = 'order-assigned-merchant';
                    $email_slug_admin = 'order-assigned-admin';
                    $to_email_address_merchant = $merchant_item_order->email;
                    $merchant_name = $merchant_item_order->merchant_no;
                    $order_no = $order_info->order_no;
                    $productData->assigned_to_merchant = 'assigned';
                    $productData->assigned_seller_1 = $merchant_id;
                    $productData->save();
                    #send sms for notification
                    $sms_params = array(
                        'company_name' => env('APP_NAME'),
                        'order_no' => $order_info->order_no,
                        'reference_no' => '',
                        'mobile_no' => [$merchant_item_order->mobile_no]
                    );
                    sendMuseeSms('seller_order_notification', $sms_params);
                    //code to send email to merchant
                    $this->sendEmailNotification($email_slug_merchant, $merchant_name, $order_no, $to_email_address_merchant);
                    //code to send email to admin regarding order
                    $this->sendEmailNotification($email_slug_admin, $merchant_name, $order_no, $to_email_address_admin);
                    //reduce product quantity of merchant
                    // $merchant_product = MerchantProduct::where([['merchant_id', $merchant_id], ['product_id', $product->product_id]])->first();
                    // $merchant_product->qty = $merchant_product->qty - $product->quantity;
                    // $merchant_product->save();
                } else {
                    $merchant_zone_order = MerchantProduct::join('merchants', 'merchants.id', '=', 'merchant_products.merchant_id')->leftJoin('zones', 'zones.id', '=', 'merchants.zone_id')->where('merchants.status', 'approved')->where('merchants.mode', 'active')->where('product_id', $product->product_id)->where('qty', '>=', $product->quantity)->where('merchant_products.status', 'published')->pluck('zone_order', 'merchant_id')->toArray();
                    if ($merchant_zone_order) {
                        $sorted_zone_order = call_user_func(function (array $a) {
                            asort($a);
                            return $a;
                        }, $merchant_zone_order);

                        $merchant_id = array_search($customer_zone_order, $sorted_zone_order);

                        if ($merchant_id) {
                            $ins['merchant_id'] = $merchant_id;
                            $ins['order_id'] = $order_info->id;
                            $ins['order_product_id'] = $product->id;
                            $profit_margin_percentage = Merchant::getProfitMarginPercentage($product->product_id, $merchant_id, null);
                            $ins['merchant_profit_margin'] = $profit_margin_percentage;
                            $ins['qty'] = $product->quantity;
                            $ins['seller_price'] = $masterProduct->seller_price ?? NULL;
                            $ins['total'] = $masterProduct->seller_price ? ($masterProduct->seller_price * $product->quantity) : NULL;

                            $merchant_order = MerchantOrder::create($ins);
                            $merchant_item_order = Merchant::find($merchant_id);
                            $email_slug_merchant = 'order-assigned-merchant';
                            $email_slug_admin = 'order-assigned-admin';
                            $to_email_address_merchant = $merchant_item_order->email;
                            $merchant_name = $merchant_item_order->merchant_no;
                            $order_no = $order_info->order_no;
                            $productData->assigned_to_merchant = 'assigned';
                            $productData->assigned_seller_1 = $merchant_id;
                            $productData->save();
                            #send sms for notification
                            $sms_params = array(
                                'company_name' => env('APP_NAME'),
                                'order_no' => $order_info->order_no,
                                'reference_no' => '',
                                'mobile_no' => [$merchant_item_order->mobile_no]
                            );
                            sendMuseeSms('seller_order_notification', $sms_params);
                            //code to send email to merchant
                            $this->sendEmailNotification($email_slug_merchant, $merchant_name, $order_no, $to_email_address_merchant);
                            //code to send email to admin regarding order
                            $this->sendEmailNotification($email_slug_admin, $merchant_name, $order_no, $to_email_address_admin);
                            //reduce product quantity of merchant
                            // $merchant_product = MerchantProduct::where([['merchant_id', $merchant_id], ['product_id', $product->product_id]])->first();
                            // $merchant_product->qty = $merchant_product->qty - $product->quantity;
                            // $merchant_product->save();
                        } else {
                            $merchant_id = 0;
                            $email_slug_admin = 'no-merchant-to-assign';
                            $order_no = $order_info->order_no;
                            // $order_info->order_status_id = 9;
                            // $order_info->status = "not_assigned";
                            $order_info->update();
                            $productData->assigned_to_merchant = 'not_assigned';
                            $productData->save();
                            #send sms for notification
                            $sms_params = array(
                                'company_name' => env('APP_NAME'),
                                'order_no' => $order_info->order_no,
                                'reference_no' => '',
                                'mobile_no' => [$globalInfo->site_mobile_no]
                            );
                            sendMuseeSms('no_merchant_to_receive', $sms_params);
                            //notify admin on no merchant has the ordered product
                            $this->sendEmailNotification($email_slug_admin, null, $order_no, $to_email_address_admin);
                        }
                    } else {
                        $merchant_id = 0;
                        $email_slug_admin = 'no-merchant-to-assign';
                        $order_no = $order_info->order_no;
                        // $order_info->order_status_id = 9;
                        // $order_info->status = "not_assigned";
                        $order_info->update();
                        $productData->assigned_to_merchant = 'not_assigned';
                        $productData->save();
                        #send sms for notification
                        $sms_params = array(
                            'company_name' => env('APP_NAME'),
                            'order_no' => $order_info->order_no,
                            'reference_no' => '',
                            'mobile_no' => [$globalInfo->site_mobile_no]
                        );
                        sendMuseeSms('no_merchant_to_receive', $sms_params);
                        //notify admin on no merchant has the ordered product
                        $this->sendEmailNotification($email_slug_admin, null, $order_no, $to_email_address_admin);
                    }
                }
            }
        }
        return $merchant_id;
    }

    /**
     * code to send emailnotification
     */
    public function sendEmailNotification($email_slug, $name = null, $order_no = null, $to_email_address)
    {
        $emailTemplate = EmailTemplate::select('email_templates.*')
            ->join('sub_categories', 'sub_categories.id', '=', 'email_templates.type_id')
            ->where('sub_categories.slug', $email_slug)->first();

        $globalInfo = GlobalSettings::first();
        $extract = array(
            'name' => $name,
            'regards' => $globalInfo->site_name,
            'order_no' => $order_no,
            'company_website' => $globalInfo->site_name,
            'company_mobile_no' => $globalInfo->site_mobile_no,
            'company_address' => $globalInfo->address
        );
        $templateMessage = $emailTemplate->message;
        $templateMessage = str_replace("{", "", addslashes($templateMessage));
        $templateMessage = str_replace("}", "", $templateMessage);
        extract($extract);
        eval("\$templateMessage = \"$templateMessage\";");

        $send_mail = new DynamicMail($templateMessage, $emailTemplate->title);
        sendEmailWithBcc($to_email_address, $send_mail);
    }

    public function generateInvoice(Request $request)
    {
        $order_no = $request->order_no;
        $order_info = Order::where('order_no', $order_no)->first();
        if ($order_info) {
            $globalInfo = GlobalSettings::first();

            $pdf = PDF::loadView('platform.invoice.index', compact('order_info', 'globalInfo'));
            Storage::put('public/invoice_order/' . $order_info->order_no . '.pdf', $pdf->output());
            return response()->json(array('error' => 0, 'status_code' => 200, 'message' => 'Invoice generated successfully', 'status' => 'success', 'success' => []), 200);
        }
        return response()->json(array('error' => 1, 'status_code' => 200, 'message' => 'Something went wrong', 'status' => 'failure', 'success' => []), 200);

    }
}
