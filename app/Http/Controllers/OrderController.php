<?php

namespace App\Http\Controllers;

use App\Exports\OrderExport;
use App\Mail\DynamicMail;
use App\Models\GlobalSettings;
use App\Models\Master\EmailTemplate;
use App\Models\Master\OrderStatus;
use App\Models\MerchantOrder;
use App\Models\MerchantOrderHistory;
use App\Models\MerchantOrderRejectReason;
use App\Models\Order;
use App\Models\OrderExchange;
use App\Models\OrderHistory;
use App\Models\OrderProduct;
use App\Models\Product\Product;
use App\Models\Seller\Merchant;
use Carbon\Carbon;
use Illuminate\Http\Request;
use DataTables;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;
use Mail;
use Illuminate\Support\Facades\Log;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = Order::selectRaw('mm_payments.order_id,
            mm_payments.payment_no,
            mm_payments.status as payment_status,
            mm_orders.*,
            sum(mm_order_products.quantity) as order_quantity,
            group_concat(mm_order_products.status) as order_status,
            mm_order_products.shipment_tracking_code,
            mm_order_products.shipment_tracking_message
            ')
                ->join('order_products', 'order_products.order_id', '=', 'orders.id')
                ->join('payments', 'payments.order_id', '=', 'orders.id')
                ->groupBy('orders.id')->orderBy('orders.id', 'desc');
            $filter_subCategory   = '';
            $status = $request->get('status');
            $coupon_status = $request->get('coupon_status');
            $keywords = $request->get('search')['value'];
            $fromDate =  $request->get('fromDate');
            $toDate =  $request->get('toDate');

            $datatables =  DataTables::of($data)
                ->filter(function ($query) use ($keywords, $coupon_status, $status, $fromDate, $toDate, $filter_subCategory) {
                    if ($status && ($coupon_status == 0 || $coupon_status == 1)) {
                        return $query->where('order_products.status', 'like', $status)
                                     ->where('orders.is_coupon', $coupon_status);
                    } elseif ($status) {
                        return $query->where('order_products.status', 'like', $status);
                    } elseif ($coupon_status == 0 || $coupon_status == 1) {
                        return $query->where('orders.is_coupon', $coupon_status);
                    } else {
                        return $query;
                    }

                    if ($keywords) {
                        $date = date('Y-m-d', strtotime($keywords));
                        return $query->where('orders.billing_name', 'like', "%{$keywords}%")
                            ->orWhere('orders.billing_email', 'like', "%{$keywords}%")
                            ->orWhere('orders.billing_mobile_no', 'like', "%{$keywords}%")
                            ->orWhere('orders.billing_address_line1', 'like', "%{$keywords}%")
                            ->orWhere('orders.billing_state', 'like', "%{$keywords}%")
                            ->orWhere('orders.order_no', 'like', "%{$keywords}%")
                            ->orWhere('orders.status', 'like', "%{$keywords}%")
                            ->orWhereDate("orders.created_at", $date);
                    }
                    if ($fromDate && $toDate) {
                        $query->whereDate("orders.created_at", ">=", $fromDate)
                            ->whereDate("orders.created_at", "<=", $toDate);
                    }
                })
                ->addIndexColumn()
                ->editColumn('billing_info', function ($row) {
                    $billing_info = '';
                    $billing_info .= '<div class="font-weight-bold">' . $row['billing_name'] . '</div>';
                    $billing_info .= '<div class="">' . $row['billing_mobile_no'] . '</div>';
                    // $billing_info .= '<div class="">'.$row['billing_address_line1'].'</div>';

                    return $billing_info;
                })

                ->editColumn('payment_status', function ($row) {
                    return ucwords($row->payment_status);
                })
                ->editColumn('status', function ($row) {
                    $orderStatus = OrderStatus::whereIn('id', explode(",", $row->order_status))->get('status_name')->first();

                    switch ($orderStatus['status_name']) {
                        case 'Order Confirmed':
                            return '<span class="badge badge-light text-success">Order Confirmed</span>';
                        case 'Order Initiate':
                            return '<span class="badge badge-light text-danger">Order Initiate</span>';
                        case 'Order Cancelled':
                            return '<span class="badge badge-light text-warning">Order Cancelled</span>';
                        case 'Order Shipped':
                            return '<span class="badge badge-light text-info">Order Shipped</span>';
                        case 'Order Placed':
                            return '<span class="badge badge-light text-primary">Order Placed</span>';
                        case 'Order Delivered':
                            return '<span style="color:#87CEEB" class="badge badge-light">Order Delivered</span>';
                        case 'Order Cancel Requested':
                            return '<span class="badge badge-light text-dark">Order Cancel Requested</span>';
                        case 'Order Rejected':
                            return '<span class="badge badge-light text-danger">Order Rejected</span>';
                        case 'Exchange Request':
                            return '<span class="badge badge-light text-success">Exchange Request</span>';
                        case 'Partial Cancel':
                            return '<span class="badge badge-light text-warning">Partial Cancel</span>';
                        case 'Exchange Accepted':
                            return '<span class="badge badge-light text-info">Exchange Accepted</span>';
                        case 'Exchange Rejected':
                            return '<span class="badge badge-light text-danger">Exchange Rejected</span>';
                        case 'Exchanged':
                            return '<span class="badge badge-light text-primary">Exchanged</span>';
                        case 'Payment Pending':
                            return '<span style="color:#87CEEB" class="badge badge-light">Payment Pending</span>';
                        default:
                            return implode(',', array_column($orderStatusArray, 'status_name'));
                    }
                })
                ->editColumn('is_coupon', function ($row) {
                    $coupon_code = $row['is_coupon'];
                    if ($coupon_code == 1) {
                        return '<span class="badge badge-light text-success">Yes</span>';
                    } else {
                        return '<span class="badge badge-light text-danger">No</span>';
                    }
                })
                ->editColumn('created_at', function ($row) {
                    $created_at = Carbon::createFromFormat('Y-m-d H:i:s', $row['created_at'])->format('d-m-Y h:i:sa');
                    return $created_at;
                })

                ->addColumn('action', function ($row) {
                    $view_btn = '<a href="javascript:void(0)" onclick="return viewOrder(' . $row->id . ')" class="btn btn-icon btn-active-primary btn-light-primary mx-1 w-30px h-30px" >
                    <i class="fa fa-eye"></i>
                </a>';

                    $view_btn .= '<a href="javascript:void(0)" onclick="return openOrderStatusModal(' . $row->id . ')" class="btn btn-icon btn-active-primary btn-light-primary mx-1 w-30px h-30px" >
                                <i class="fa fa-edit"></i>
                            </a>';

                    $view_btn .= '<a target="_blank" href="' . asset('storage/invoice_order/' . $row->order_no . '.pdf') . '" tooltip="Download Invoice"  class="btn btn-icon btn-active-success btn-light-success mx-1 w-30px h-30px" >
                                    <i class="fa fa-download"></i>
                                </a>';

                    return $view_btn;
                })
                ->rawColumns(['action', 'status','is_coupon', 'billing_info', 'payment_status', 'order_status', 'created_at']);

            return $datatables->make(true);
        }
        $breadCrum = array('Order');
        $title      = 'Order';
        return view('platform.order.index', compact('title', 'breadCrum'));
    }

    public function orderView(Request $request)
    {
        $order_id = $request->id;

        try {

            DB::beginTransaction();

            // Get and update view flag from orders table

            $order_info = Order::find($order_id);
            if ($order_info->is_viewed_order !== true) {
                $order_info->is_viewed_order = true;
                $order_info->save();
            }
            $modal_title = 'View Order';
            $globalInfo = GlobalSettings::first();
            $view_order = view('platform.order.view_order', compact('order_info', 'globalInfo'));

            DB::commit();

            return view('platform.order.view_modal', compact('view_order', 'modal_title'));
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json(['error' => 'An error occurred. Please try again.']);
        }
    }


    public function openOrderStatusModal(Request $request)
    {

        $order_id = $request->id;
        $order_status_id = $request->order_status_id;
        $modal_title  = 'Update Order Status';
        $info = Order::find($order_id);

        if ($info->is_viewed_order !== true) {
            $info->is_viewed_order = true;
            $info->save();
        }
        $order_product = OrderProduct::where('order_id', $order_id)->get();

        $merchants_list_to_assign = $this->getSellerListToAssign();


        // $order_history = OrderHistory::where('order_id', $order_id)->where('show_in_front', 1)->orderBy('order', 'asc')->get();
        // $merchant_order_history = MerchantOrderHistory::where('order_id', $order_id)->get();
        $order_status_info = OrderStatus::where('status', 'published')->get();
        // $order_status_info = MerchantOrderStatus::select('id', 'order_status', 'order_status_name')->get();
        $order_reject_reasons = MerchantOrderRejectReason::select('id', 'reason')->get();

        return view('platform.order.order_status_modal', compact('info', 'order_status_info', 'order_product', 'modal_title', 'order_reject_reasons', 'merchants_list_to_assign'));
    }

    public function cancelRequestStatusModal(Request $request)
    {

        $id = $request->id;
        $order_status_id = $request->order_status_id;
        $modal_title        = 'Update Order Status';

        $info = OrderProduct::find($id);
        $order_status_info = OrderStatus::where('status', 'published')->get();

        return view('platform.order.cancel_request_status_modal', compact('info', 'order_status_info'));
    }

    public function changeOrderStatus(Request $request)
    {

        $id             = $request->id;
        $orderProductDetails = json_decode($request->order_product_details);

        $info = Order::find($id);
        $action = '';

        foreach ($orderProductDetails as $orderProductDetail) {

            $orderProduct = OrderProduct::find($orderProductDetail->item_id);
            $orderStatus = OrderStatus::find($orderProductDetail->order_status_id);
            // log::info($orderStatus);
            if ($orderStatus) {
                $action = $orderStatus->status_name;
                // log::info($orderStatus->status_name);
            }
            // log::info('order product status in db'. $orderProduct->status);
            // log::info('order product status from form'. $orderProductDetail->order_status_id);
            if($orderProduct->status != $orderProductDetail->order_status_id){
                $ins['order_id'] = $request->id;
                $ins['product_id'] = $orderProduct->product_id;
                $ins['action'] = $action;
                $ins['order_status_id'] = $orderProductDetail->order_status_id;
                $ins['description'] = $request->description;
                OrderHistory::create($ins);
            }
            $merchantOrder = MerchantOrder::where('order_id', $id)->where('order_product_id', $orderProductDetail->item_id)->where('order_status', '!=', 'reject')->first();

            if (isset($orderProductDetail->merchant_list_id) && (!empty($orderProductDetail->merchant_list_id))) {
                $masterProduct = Product::find($orderProduct->product_id);
                $ins['merchant_id'] = $orderProductDetail->merchant_list_id;
                $ins['order_id'] = $orderProductDetail->id;
                $ins['order_product_id'] = $orderProductDetail->item_id;
                $profit_margin_percentage = Merchant::getProfitMarginPercentage($orderProduct->product_id, $orderProductDetail->merchant_list_id, null);
                $ins['merchant_profit_margin'] = $profit_margin_percentage;
                $ins['qty'] = $orderProduct->quantity;
                $ins['seller_price'] = $masterProduct->seller_price ?? NULL;
                $ins['total'] = $masterProduct->seller_price ? ($masterProduct->seller_price * $orderProduct->quantity) : NULL;
                MerchantOrder::create($ins);
                $orderProduct->assigned_to_merchant = 'assigned';
                $orderProduct->assigned_seller_1 = $orderProductDetail->merchant_list_id;
            }

            if ($orderProductDetail->order_status_id == 4) {
                $orderProduct->shipment_tracking_code = $orderProductDetail->shipment_tracking_code;
                $orderProduct->shipment_tracking_message = $orderProductDetail->shipment_tracking_message;
            }
            $orderProduct->status = $orderProductDetail->order_status_id;
            $orderProduct->save();


            // dd($orderProductDetail->order_status_id);
            switch ($orderProductDetail->order_status_id) {
                case '1':
                    // $action = 'Order Initiated';
                    $info->status = 'pending';
                    break;

                case '2':
                    // $action = 'Order Placed';
                    $info->status = 'placed';
                    break;

                case '3':
                    // $action = 'Order Cancelled';
                    $info->status = 'cancelled';
                    if ($merchantOrder !== null) {
                        $merchantOrder->order_status = 'cancel';
                    }
                    break;

                case '4':

                    if($orderProduct->status != $orderProductDetail->order_status_id){
                        // $action = 'Order Shipped';
                        /****
                         * 1.send email for order placed
                         * 2.send sms for notification
                         */
                        #generate invoice
                        $globalInfo = GlobalSettings::first();
                        #send mail
                        $emailTemplate = EmailTemplate::select('email_templates.*')
                            ->join('sub_categories', 'sub_categories.id', '=', 'email_templates.type_id')
                            ->where('sub_categories.slug', 'order-shipped')->first();

                        $globalInfo = GlobalSettings::first();

                        $extract = array(
                            'name' => $info->billing_name,
                            'regards' => $globalInfo->site_name,
                            'company_website' => '',
                            'company_mobile_no' => $globalInfo->site_mobile_no,
                            'company_email' => $globalInfo->site_email,
                            'company_address' => $globalInfo->address,
                            'customer_login_url' => env('WEBSITE_LOGIN_URL'),
                            'order_no' => $info->order_no
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

                        $send_mail = new DynamicMail($templateMessage, $title);
                        // return $send_mail->render();
                        sendEmailWithBcc($info->billing_email, $send_mail);

                        #send sms for notification
                        $sms_params = array(
                            'name' => $info->billing_name,
                            'order_no' => $info->order_no,
                            'tracking_url' => env('WEBSITE_LOGIN_URL'),
                            'mobile_no' => [$info->billing_mobile_no]
                        );
                        sendMuseeSms('shipping', $sms_params);

                        $info->status = 'shipped';
                        if ($merchantOrder !== null) {
                            $merchantOrder->order_status = 'ship';
                        }
                    }

                    break;

                case '5':

                    // $action = 'Order Delivered';
                     if($orderProduct->status == $orderProductDetail->order_status_id){
                        // dd(1);
                        $globalInfo = GlobalSettings::first();
                        #send mail
                        $emailTemplate = EmailTemplate::select('email_templates.*')
                            ->join('sub_categories', 'sub_categories.id', '=', 'email_templates.type_id')
                            ->where('sub_categories.slug', 'order-delivered')->first();
                        $globalInfo = GlobalSettings::first();
                        $extract = array(
                            'name' => $info->billing_name,
                            'regards' => $globalInfo->site_name,
                            'company_website' => '',
                            'company_mobile_no' => $globalInfo->site_mobile_no,
                            'company_email' => $globalInfo->site_email,
                            'company_address' => $globalInfo->address,
                            'customer_login_url' => env('WEBSITE_LOGIN_URL'),
                            'order_no' => $info->order_no,
                            'order_id' => $info->order_id
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
                        $send_mail = new DynamicMail($templateMessage, $title);
                        // dd($send_mail);
                        // return $send_mail->render();
                        sendEmailWithBcc($info->billing_email, $send_mail);
                    }

                    $info->status = 'delivered';
                    if ($merchantOrder !== null) {
                        $merchantOrder->order_status = 'deliver';
                    }
                    break;

                default:
                    # code...
                    break;
            }

            if ($merchantOrder !== null) {
                $merchantOrder->save();
            }
            $info->update();

        }

        $message    = (isset($id) && !empty($id)) ? 'Updated Successfully' : 'Added successfully';
        $error = 0;

        return response()->json(['error' => $error, 'message' => $message]);
    }


    public function changeCancelRequestStatus(Request $request)
    {
        $id             = $request->id;
        $order_id       = $request->order_id;
        $product_id     = $request->product_id;
        $validator      = Validator::make($request->all(), [
            'order_status_id' => 'required|string',
            'description' => 'required|string',

        ]);
        if ($validator->passes()) {
            $orderProduct = OrderProduct::find($id);
            $info = Order::find($order_id);
            $merchantOrder = MerchantOrder::where('order_id', $order_id)->where('order_product_id', $id)->first();
            //dd($merchantOrder);
            $info->order_status_id = $request->order_status_id;
            $orderProduct->status = $request->order_status_id;
            $orderStatus = OrderStatus::find($request->order_status_id);
            if ($orderStatus) {
                $action = $orderStatus->status_name;
            }
            switch ($request->order_status_id) {
                case '1':
                    // $action = 'Order Initiated';
                    $info->status = 'pending';
                    break;

                case '2':
                    // $action = 'Order Placed';
                    $info->status = 'placed';
                    break;

                case '3':
                    if (count($info->orderItems) == 1) {
                        // $action = 'Order Cancelled';
                        $info->status = 'cancelled';
                        $info->order_status_id = 3;
                    } else {
                        // $action = 'Partial Cancel';
                        $info->status = 'partial_cancel';
                        $info->order_status_id = 9;
                    }
                    $orderProduct->status = 3;
                    if ($merchantOrder !== null) {
                        $merchantOrder->order_status = 'cancel';
                    }
                    break;

                case '4':
                    // $action = 'Order Shipped';
                    /****
                     * 1.send email for order placed
                     * 2.send sms for notification
                     */
                    #generate invoice
                    $globalInfo = GlobalSettings::first();

                    #send mail
                    $emailTemplate = EmailTemplate::select('email_templates.*')
                        ->join('sub_categories', 'sub_categories.id', '=', 'email_templates.type_id')
                        ->where('sub_categories.slug', 'order-shipped')->first();

                    $globalInfo = GlobalSettings::first();

                    $extract = array(
                        'name' => $info->billing_name,
                        'regards' => $globalInfo->site_name,
                        'company_website' => '',
                        'company_mobile_no' => $globalInfo->site_mobile_no,
                        'company_email' => $globalInfo->site_email,
                        'company_address' => $globalInfo->address,
                        'customer_login_url' => env('WEBSITE_LOGIN_URL'),
                        'order_no' => $info->order_no
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

                    $send_mail = new DynamicMail($templateMessage, $title);
                    // return $send_mail->render();
                    sendEmailWithBcc($info->billing_email, $send_email);
                    #send sms for notification
                    $sms_params = array(
                        'name' => $info->billing_name,
                        'order_no' => $info->order_no,
                        'tracking_url' => env('WEBSITE_LOGIN_URL'),
                        'mobile_no' => [$info->billing_mobile_no]
                    );
                    sendMuseeSms('shipping', $sms_params);

                    $info->status = 'shipped';
                    if ($merchantOrder !== null) {
                        $merchantOrder->order_status = 'ship';
                    }
                    break;

                case '5':
                    // $action = 'Order Delivered';
                    $info->status = 'delivered';
                    if ($merchantOrder !== null) {
                        $merchantOrder->order_status = 'deliver';
                    }
                    break;
                case '9':
                    if (count($info->orderItems) == 1) {
                        // $action = 'Order Cancelled';
                        $info->status = 'cancelled';
                        $info->order_status_id = 3;
                    } else {
                        // $action = 'Partial Cancel';
                        $info->status = 'partial_cancel';
                        $info->order_status_id = 9;
                    }
                    $orderProduct->status = 3;
                    if ($merchantOrder !== null) {
                        $merchantOrder->order_status = 'cancel';
                    }
                    break;

                default:
                    # code...
                    break;
            }
            $info->update();
            $orderProduct->update();
            //dd($merchantOrder);
            if ($merchantOrder !== null) {
                $merchantOrder->save();
            }
            $ins['order_id']     = $request->order_id;
            $ins['action']       = $action ?? '';
            $ins['description']  = $request->description;
            $ins['order_status_id']  = $request->order_status_id;
            $ins['product_id']     = $product_id;

            OrderHistory::create($ins);
            $message    = (isset($id) && !empty($id)) ? 'Updated Successfully' : 'Added successfully';
            $error = 0;
        } else {
            $error      = 1;
            $message    = $validator->errors()->all();
        }
        return response()->json(['error' => $error, 'message' => $message]);
    }

    public function export()
    {
        return Excel::download(new OrderExport, 'orders.xlsx');
    }

    public function  cancelRequested(Request $request)
    {
        if ($request->ajax()) {
            $data = OrderProduct::select('order_products.*', 'orders.order_no', 'orders.billing_name', 'orders.billing_email', 'orders.billing_mobile_no', 'orders.status as order_status', 'payments.payment_no', 'payments.status as payment_status', 'order_products.quantity as order_quantity', 'order_products.assigned_seller_1 as seller_1', 'order_products.assigned_seller_2 as seller_2')
                ->leftJoin('orders', 'orders.id', '=', 'order_products.order_id')
                ->leftJoin('payments', 'payments.order_id', '=', 'orders.id')
                ->where('order_products.status', 6)
                ->orderBy('order_products.id', 'desc');

            $filter_subCategory   = '';
            $status = '6';
            $keywords = $request->get('search')['value'];
            $fromDate =  $request->get('fromDate');
            $toDate =  $request->get('toDate');

            $datatables =  DataTables::of($data)
                ->filter(function ($query) use ($keywords, $status, $fromDate, $toDate, $filter_subCategory) {
                    /* if ($status) {
                        return $query->where('order_products.status', 'like', $status);
                    }
                    if ($keywords) {
                        $date = date('Y-m-d', strtotime($keywords));
                        return $query->where('orders.billing_name', 'like', "%{$keywords}%")
                            ->orWhere('orders.billing_email', 'like', "%{$keywords}%")
                            ->orWhere('orders.billing_mobile_no', 'like', "%{$keywords}%")
                            ->orWhere('orders.billing_address_line1', 'like', "%{$keywords}%")
                            ->orWhere('orders.billing_state', 'like', "%{$keywords}%")
                            ->orWhere('orders.order_no', 'like', "%{$keywords}%")
                            //->orWhere('orders.status', 'like', "%{$keywords}%")
                            ->orWhereDate("orders.created_at", $date);
                    }
                    if ($fromDate && $toDate) {
                        $query->whereDate("orders.created_at", ">=", $fromDate)
                            ->whereDate("orders.created_at", "<=", $toDate);
                    }*/
                })
                ->addIndexColumn()
                ->editColumn('billing_info', function ($row) {
                    $billing_info = '';
                    $billing_info .= '<div class="font-weight-bold">' . $row['billing_name'] . '</div>';
                    $billing_info .= '<div class="">' . $row['billing_mobile_no'] . '</div>';
                    // $billing_info .= '<div class="">'.$row['billing_address_line1'].'</div>';
                    return $billing_info;
                })

                ->editColumn('seller_info', function ($row) {
                    $sellerId = ($row['seller_2'] == null) ? $row['seller_1'] : $row['seller_2'];
                    $seller = $this->getSellerDetails($sellerId);
                    $sellerInfo = $sellerId;
                    if ($seller != false) {
                        $sellerInfo .= '<div class="font-weight-bold">' . $seller->first_name ?? '' . '' . $seller->last_name ?? '' . '</div>';
                        $sellerInfo .= '<div class="">' . $seller->email ?? '' . '</div>';
                        $sellerInfo .= '<div class="">' . $seller->mobile_no ?? '' . '</div>';
                    }
                    return $sellerInfo;
                })
                ->editColumn('product_name', function ($row) {
                    $product = '';
                    $product .= '<div class="font-weight-bold">' . $row['product_name'] . '</div>';
                    $product .= '<div class=""> Qty -' . $row['order_quantity'] . '</div>';
                    return $product;
                })
                ->editColumn('payment_status', function ($row) {
                    return ucwords($row->payment_status);
                })
                ->editColumn('status', function ($row) {
                    $orderStatus =$row->order_status;
                    switch ($orderStatus) {
                        case 'accept':
                            return '<span class="badge badge-light text-success">Accepted</span>';
                        case 'pending':
                            return '<span class="badge badge-light text-warning">Pending</span>';
                        case 'ship':
                            return '<span class="badge badge-light text-dark">Shipped</span>';
                        case 'reject':
                            return '<span class="badge badge-light text-danger">Rejected</span>';
                        case 'deliver':
                            return '<span class="badge badge-light text-info">Delivered</span>';
                        case 'exchange_requested':
                            return '<span style="color:#87CEEB" class="badge badge-light">Exchange Requested</span>';
                        case 'cancelled':
                            return '<span class="badge badge-light text-danger">Cancelled</span>';
                        case 'cancel_requested':
                            return '<span class="badge badge-light text-muted">Cancel Requested</span>';
                        default:
                            return ucwords($orderStatus);
                    }
                })
                ->editColumn('created_at', function ($row) {
                    $created_at = Carbon::createFromFormat('Y-m-d H:i:s', $row['created_at'])->format('d-m-Y h:i a');
                    return $created_at;
                })

                ->addColumn('action', function ($row) {
                    $view_btn = '<a href="javascript:void(0)" onclick="return viewOrder(' . $row->order_id . ')" class="btn btn-icon btn-active-primary btn-light-primary mx-1 w-30px h-30px" >
                    <i class="fa fa-eye"></i>
                </a>';

                    $view_btn .= '<a href="javascript:void(0)" onclick="return changeStatusModal(' . $row->id . ')" class="btn btn-icon btn-active-primary btn-light-primary mx-1 w-30px h-30px" >
                                <i class="fa fa-edit"></i>
                            </a>';

                    $view_btn .= '<a target="_blank" href="' . asset('storage/invoice_order/' . $row->order_no . '.pdf') . '" tooltip="Download Invoice"  class="btn btn-icon btn-active-success btn-light-success mx-1 w-30px h-30px" >
                                    <i class="fa fa-download"></i>
                                </a>';

                    return $view_btn;
                })
                ->rawColumns(['action', 'status', 'billing_info', 'seller_info', 'product_name', 'payment_status', 'order_status', 'created_at']);
            return $datatables->make(true);
        }
        $breadCrum = array('Order');
        $title      = 'Cancel Requested';
        return view('platform.order.cancel_requested', compact('title', 'breadCrum'));
    }

    public function cancelView(Request $request)
    {
    }

    protected function getSellerDetails($id)
    {
        $sql = Merchant::find($id);
        if ($sql != null) {
            return $sql;
        }
        return false;
    }

    public function getSellerListToAssign()
    {
        return Merchant::where('status', 'approved')->where('mode', 'active')->get();
    }
}
