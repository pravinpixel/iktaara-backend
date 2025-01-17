<?php

namespace App\Http\Controllers;

use App\Exports\OrderCancelReasonExport;
use App\Models\Order;
use Illuminate\Http\Request;
use DataTables;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;
use Mail;
use Carbon\Carbon;
use App\Models\OrderExchangeReason;

class ExchangeStatusController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = OrderExchangeReason::orderBy('order_by', 'desc');
            $filter_subCategory   = '';
            $status = $request->get('status');
            $keywords = $request->get('search')['value'];
            $datatables =  DataTables::of($data)
                ->filter(function ($query) use ($keywords, $status, $filter_subCategory) {
                    if ($status) {
                        return $query->where('order_exchange_reasons.status', 'like', $status);
                    }
                    if ($keywords) {
                        $date = date('Y-m-d', strtotime($keywords));
                        return $query->where('order_exchange_reasons.name', 'like', "%{$keywords}%")
                            ->orWhere('order_exchange_reasons.description', 'like', "%{$keywords}%")
                            ->orWhereDate("order_exchange_reasons.created_at", $date);
                    }
                })
                ->addIndexColumn()

                ->editColumn('status', function ($row) {
                    $status = '<a href="javascript:void(0);" class="badge badge-light-' . (($row->status == 'published') ? 'success' : 'danger') . '" tooltip="Click to ' . (($row->status == 'published') ? 'Unpublish' : 'Publish') . '" onclick="return commonChangeStatus(' . $row->id . ',\'' . (($row->status == 'published') ? 'unpublished' : 'published') . '\', \'exchange-status\')">' . ucfirst($row->status) . '</a>';
                    return $status;
                })
                ->editColumn('created_at', function ($row) {
                    $created_at = Carbon::createFromFormat('Y-m-d H:i:s', $row['created_at'])->format('d-m-Y');
                    return $created_at;
                })

                ->addColumn('action', function ($row) {
                    $edit_btn = '<a href="javascript:void(0);" onclick="return  openForm(\'exchange-status\',' . $row->id . ')" class="btn btn-icon btn-active-primary btn-light-primary mx-1 w-30px h-30px" > 
                    <i class="fa fa-edit"></i>
                </a>';
                    $del_btn = '<a href="javascript:void(0);" onclick="return commonDelete(' . $row->id . ', \'exchange-status\')" class="btn btn-icon btn-active-danger btn-light-danger mx-1 w-30px h-30px" > 
                <i class="fa fa-trash"></i></a>';

                    return $edit_btn . $del_btn;
                })
                ->rawColumns(['action', 'status', 'created_at']);
            return $datatables->make(true);
        }
        $breadCrum = array('Order', 'Order Exchange Reason');
        $title      = 'Order Exchange Reason';
        return view('platform.exchange_status.index', compact('title', 'breadCrum'));
    }

    public function modalAddEdit(Request $request)
    {
        $id                 = $request->id;
        $info               = '';
        $modal_title        = 'Add Order Exchange Reason';
        if (isset($id) && !empty($id)) {
            $info           = OrderExchangeReason::find($id);
            $modal_title    = 'Update Order Exchange Reason';
        }

        return view('platform.exchange_status.add_edit_modal', compact('info', 'modal_title'));
    }
    public function saveForm(Request $request, $id = null)
    {
        $id             = $request->id;
        $validator      = Validator::make($request->all(), [
            'name' => 'required|string|unique:order_exchange_reasons,name,' . $id . ',id,deleted_at,NULL',
            'order_by' => 'required|unique:order_exchange_reasons,order_by,' . $id . ',id,deleted_at,NULL'
        ]);
        $exchange_order_id      = '';

        if ($validator->passes()) {

            $ins['name']               = $request->name;
            $ins['description']         = $request->description;
            $ins['order_by']            = $request->order_by ?? 0;

            if ($request->status == "1") {
                $ins['status']          = 'published';
            } else {
                $ins['status']          = 'unpublished';
            }

            $error                      = 0;
            $info                       = OrderExchangeReason::updateOrCreate(['id' => $id], $ins);
            $exchange_order_id            = $info->id;

            $message                    = (isset($id) && !empty($id)) ? 'Updated Successfully' : 'Added successfully';
        } else {
            $error                      = 1;
            $message                    = $validator->errors()->all();
        }
        return response()->json(['error' => $error, 'message' => $message, 'exchange_order_id' => $exchange_order_id]);
    }
    public function changeStatus(Request $request)
    {
        $id             = $request->id;
        $status         = $request->status;
        $info           = OrderExchangeReason::find($id);
        $info->status   = $status;
        $info->update();
        return response()->json(['message' => "You changed the Order Exchange status!", 'status' => 1]);
    }

    public function delete(Request $request)
    {
        $id         = $request->id;
        $info       = OrderExchangeReason::find($id);
        $info->delete();
        return response()->json(['message' => "Successfully deleted Order Exchange Reason!", 'status' => 1]);
    }

    public function view(Request $request)
    {
    }

    public function export()
    {
        return Excel::download(new OrderCancelReasonExport, 'banner.xlsx');
    }
}
