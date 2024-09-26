<?php

namespace App\Http\Controllers;

use App\Models\OrderRejectReason;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;

class OrderRejectController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {

            $data = OrderRejectReason::orderBy('created_at', 'desc');
            $filter_subCategory   = '';
            $status = $request->get('status');
            $keywords = $request->get('search')['value'];
            $datatables =  DataTables::of($data)
                ->filter(function ($query) use ($keywords, $status, $filter_subCategory) {
                    if ($status) {
                        return $query->where('merchant_order_reject_reasons.status', 'like', $status);
                    }
                    if ($keywords) {
                        $date = date('Y-m-d', strtotime($keywords));
                        return $query->where('merchant_order_reject_reasons.reason','like',"%{$keywords}%")
                                ->orWhereDate("merchant_order_reject_reasons.created_at", $date);
                    }
                })
                ->addIndexColumn()

                ->editColumn('status', function ($row) {
                    $status = '<a href="javascript:void(0);" class="badge badge-light-'.(($row->status == 'published') ? 'success': 'danger').'" tooltip="Click to '.(($row->status == 'published') ? 'Unpublish' : 'Publish').'" onclick="return commonChangeStatus(' . $row->id . ',\''.(($row->status == 'published') ? 'unpublished': 'published').'\', \'order-reject\')">'.ucfirst($row->status).'</a>';
                    return $status;
                })
                ->editColumn('created_at', function ($row) {
                    $created_at = Carbon::createFromFormat('Y-m-d H:i:s', $row['created_at'])->format('d-m-Y');
                    return $created_at;
                })

                ->addColumn('action', function ($row) {
                    $edit_btn = '<a href="javascript:void(0);" onclick="return  openForm(\'order-reject\',' . $row->id . ')" class="btn btn-icon btn-active-primary btn-light-primary mx-1 w-30px h-30px" >
                    <i class="fa fa-edit"></i>
                </a>';
                    $del_btn = '<a href="javascript:void(0);" onclick="return commonDelete(' . $row->id . ', \'order-reject\')" class="btn btn-icon btn-active-danger btn-light-danger mx-1 w-30px h-30px" >
                <i class="fa fa-trash"></i></a>';

                    return $edit_btn . $del_btn;
                })
                ->rawColumns(['action', 'status', 'created_at']);
            return $datatables->make(true);
        }
        $breadCrum = array('Order','Order Reject Reason');
        $title      = 'Order Reject Reason';
        return view('platform.order_reject.index',compact('title', 'breadCrum'));
    }

    public function modalAddEdit(Request $request)
    {
        $id                 = $request->id;
        $info               = '';
        $modal_title        = 'Add Order Reject Reason';
        if (isset($id) && !empty($id)) {
            $info           = OrderRejectReason::find($id);
            $modal_title    = 'Update Order Reject Reason';
        }

        return view('platform.order_reject.add_edit_modal', compact('info', 'modal_title'));
    }

    public function saveForm(Request $request,$id = null)
    {
        $id = $request->id;
        $validator = Validator::make($request->all(), [
                                'reason' => 'required',
                            ]);

        if ($validator->passes()) {

            $data['reason'] = $request->reason;

            if($request->status == "1")
            {
                $data['status'] = 'published';
            } else {
                $data['status'] = 'unpublished';
            }

            $error                      = 0;
            OrderRejectReason::updateOrCreate(['id' => $id], $data);
            $message                    = (isset($id) && !empty($id)) ? 'Updated Successfully' : 'Added successfully';
        } else {
            $error                      = 1;
            $message                    = $validator->errors()->all();
        }
        // return response()->json(['error' => $error, 'message' => $message, 'cancel_order_id' => $cancel_order_id]);
        return response()->json([ 'error' => $error, 'message' => $message ]);

    }

    public function changeStatus(Request $request)
    {
        $id             = $request->id;
        $status         = $request->status;
        $info           = OrderRejectReason::find($id);
        $info->status   = $status;
        $info->update();
        return response()->json([ 'message' => "You changed the Order Reject status!", 'status' => 1 ]);

    }

    public function delete(Request $request)
    {
        OrderRejectReason::find($request->id)->delete();
        return response()->json([ 'message' => "Successfully deleted Order Reject Reason!", 'status' => 1 ]);
    }
}
