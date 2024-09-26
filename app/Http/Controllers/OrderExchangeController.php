<?php

namespace App\Http\Controllers;

use App\Events\ProductExchange;
use App\Models\Master\OrderStatus;
use App\Models\MerchantOrder;
use Illuminate\Http\Request;
use Carbon\Carbon;
use DataTables;
use App\Models\OrderExchange;
use App\Models\OrderHistory;
use App\Models\OrderProduct;
use App\Models\Seller\Merchant;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class OrderExchangeController extends Controller
{
    public function  index(Request $request)
    {
        if ($request->ajax()) {
            $data = OrderExchange::select('order_exchange.*', 'orders.order_no', 'orders.billing_name', 'orders.billing_email', 'orders.billing_mobile_no', 'orders.status as order_status', 'payments.payment_no', 'payments.status as payment_status', 'products.product_name as product_name', 'order_products.quantity as order_quantity')
                ->leftJoin('orders', 'orders.id', '=', 'order_exchange.order_id')
                ->leftJoin('products', 'products.id', '=', 'order_exchange.product_id')
                ->leftJoin('order_products', 'order_products.id', '=', 'order_exchange.product_id')
                ->leftJoin('payments', 'payments.order_id', '=', 'order_exchange.order_id')
                ->orderBy('order_exchange.id', 'desc');



            $filter_subCategory   = '';
            $status = $request->get('status');
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
                    //$sellerId = ($row['seller_2'] == null) ? $row['seller_1'] : $row['seller_2'];
                    $sellerInfo = '';
                    if (isset($row->seller_id) && $row->seller_id != NULL) {
                        $seller =  $this->getSellerDetails($row->seller_id);
                        if ($seller != false) {
                            $sellerInfo .= '<div class="font-weight-bold">' . $seller->first_name ?? '' . '' . $seller->last_name ?? '' . '</div>';
                            $sellerInfo .= '<div class="">' . $seller->email ?? '' . '</div>';
                            $sellerInfo .= '<div class="">' . $seller->mobile_no ?? '' . '</div>';
                        }
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
                    $Status = OrderExchange::EXCHANGE_STATUS[$row['status']];    
                    switch ($Status) {
                        case 'approved':
                            return '<span class="badge badge-light text-success">Approved</span>';
                        case 'Rejected':
                            return '<span class="badge badge-light text-danger">Rejected</span>';
                            case 'pending':
                            return '<span class="badge badge-light text-warning">Pending</span>';
                        default:
                            return ucwords($Status);
                    }
                })
                ->editColumn('created_at', function ($row) {
                    $created_at = Carbon::createFromFormat('Y-m-d H:i:s', $row['created_at'])->format('d-m-Y h:i a');
                    return $created_at;
                })

                ->addColumn('action', function ($row) {
                    $view_btn = '<a href="javascript:void(0)" onclick="return view(' . $row->id . ')" class="btn btn-icon btn-active-primary btn-light-primary mx-1 w-30px h-30px" >
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
                ->rawColumns(['action', 'status', 'billing_info', 'seller_info', 'product_name', 'payment_status', 'status', 'created_at']);
            return $datatables->make(true);
        }
        $breadCrum = array('Order', 'Exchange');
        $title      = 'Exchange Requested';
        return view('platform.order.exchange.index', compact('title', 'breadCrum'));
    }

    public function changeStatus(Request $request)
    {
        $id             = $request->id;
        $breadCrum = array('Order', 'Exchange');
        $modal_title      = 'Update Exchange Request Status';
        $orderExchange = OrderExchange::where('id', $id)
                                        ->select('status')
                                        ->get();

        return view('platform.order.exchange.status_modal', compact('modal_title', 'id', 'breadCrum', 'orderExchange'));
    }

    public function updateStatus(Request $request)
    {
        $id = $request->id;
        $status = $request->status;
        $description = $request->description;

        $exchange = OrderExchange::find($id);

        DB::beginTransaction();

        try {
            DB::commit();

            $exchange->status = $request->status;
            $exchange->save();

            $orderProduct = OrderProduct::find($exchange->order_item_id);
            if ($status == 1) {
                $orderProduct->status = 11;
                $orderStatus = OrderStatus::find(11);
                if($orderStatus){
                    $action = $orderStatus->status_name;
                }
                OrderHistory::create([
                    'order_id' => $orderProduct->order_id,
                    'product_id' => $orderProduct->product_id,
                    'action' => $action ?? '',
                    'order_status_id' => 11,
                    'description' => $description
                ]);

                if ($exchange->seller_id != NULL) {
                    $merchant = MerchantOrder::where('order_id', $exchange->order_id)->where('order_product_id', $exchange->order_item_id)->where('merchant_id', $exchange->seller_id)->first();
                    $merchant->order_status = 'exchange_accept';
                    $merchant->save();
                }
            } elseif ($status == 2) {
                $orderProduct->status = 12;
                $orderStatus = OrderStatus::find(11);
                if($orderStatus){
                    $action = $orderStatus->status_name;
                }
                OrderHistory::create([
                    'order_id' => $orderProduct->order_id,
                    'product_id' => $orderProduct->product_id,
                    'action' => $action ?? '',
                    'order_status_id' => 12,
                    'description' => $description
                ]);
                if ($exchange->seller_id != NULL) {
                    $merchant = MerchantOrder::where('order_id', $exchange->order_id)->where('order_product_id', $exchange->order_item_id)->where('merchant_id', $exchange->seller_id)->first();
                    $merchant->order_status = 'exchange_reject';
                    $merchant->save();
                }
            }
            $orderProduct->save();

            $eventData = [
                'item_id' => $orderProduct->id,
                'order_id' => $exchange->order_id,
                'product_id' => $exchange->product_id,
                'customer_id' => $exchange->customer_id,
                'seller_id' => $exchange->seller_id,
                'reason' => $exchange->reason,
                'status' => $exchange->status,
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
        $response['status_code'] = 200;
        $response['error'] = 0;
        $response['message'] = 'Status Updated Successfully';
        $response['status'] = 'success';
        $response['data'] = [];

        return new Response($response, 200);
    }

    public function view(Request $request)
    {
        $id = $request->id;
        $modal_title        = 'Exchange Request Details';
        $info =
            OrderExchange::select('order_exchange.*', 'orders.order_no', 'orders.billing_name', 'orders.billing_email', 'orders.billing_mobile_no', 'orders.status as order_status', 'payments.payment_no', 'payments.status as payment_status', 'order_products.quantity as order_quantity', 'products.product_name', 'products.hsn_code', 'order_exchange_reasons.name as reason_name')
            ->leftJoin('orders', 'orders.id', '=', 'order_exchange.order_id')
            ->leftJoin('products', 'products.id', '=', 'order_exchange.product_id')
            ->leftJoin('order_products', 'order_products.id', '=', 'order_exchange.product_id')
            ->leftJoin('payments', 'payments.order_id', '=', 'order_exchange.order_id')
            ->leftJoin('order_exchange_reasons','order_exchange_reasons.id', '=', 'order_exchange.reason_id')
            ->where('order_exchange.id', $id)
            ->first();

        $seller = ($info->seller_id == null) ? [] : $this->getSellerDetails($info->seller_id);

        return view('platform.order.exchange.view', compact('info', 'seller', 'modal_title'));
    }

    public function delete(Request $request, OrderExchange $exchange)
    {
        $id         = $request->id;
        $exchange       = OrderExchange::find($id);
        $exchange->delete();
        return response()->json(['message' => "Successfully deleted!", 'status' => 1]);
    }

    protected function getSellerDetails($id)
    {
        $sql = Merchant::find($id);
        if ($sql != null) {
            return $sql;
        }
        return false;
    }
}
