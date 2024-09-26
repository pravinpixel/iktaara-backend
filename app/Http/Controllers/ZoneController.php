<?php

namespace App\Http\Controllers;

use App\Models\Master\City;
use App\Models\Master\Country;
use App\Models\Seller\Merchant;
use App\Models\Master\Pincode;
use App\Models\Master\State;
use PDF;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Mail\DynamicMail;
use App\Models\GlobalSettings;
use App\Models\Master\EmailTemplate;
use App\Models\Seller\Area;
use App\Models\Zone;
use App\Models\ZoneState;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use DataTables;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

class ZoneController extends Controller
{

    // use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = RouteServiceProvider::HOME;

    public function index(Request $request)
    {
        $title                  = "Zones";
        $breadCrum              = array('Zones', 'Zones');

        if ($request->ajax()) {
            $data               = Zone::with('collectionStates')->get();//Zone::select('zone_id','zone_name','zone_order', 'status')->selectRaw('GROUP_CONCAT(state) as states')->groupBy('zone_id')->get();
            $status             = $request->get('status');
            $keywords           = $request->get('search')['value'];
            $datatables         = Datatables::of($data)
            ->filter(function ($query) use ($keywords, $status) {
                if ($status) {
                    return $query->where('zones.status',$status);
                }
                if ($keywords) {

                    if( !strpos($keywords, '.')) {
                        $date = date('Y-m-d', strtotime($keywords));
                    }
                    $query->where('zones.state', 'like', "%{$keywords}%");
                    if( isset( $date )) {
                        $query->orWhereDate("zones.created_at", $date);
                    }

                    return $query;
                }
            })
                ->addIndexColumn()
                // ->editColumn('state', function ($row) {
                //     $state_id_array = explode(',', $row->states);
                //     $states = State::select('state_name')->whereIn('id', $state_id_array)->get();
                //     $state_string = [];
                //     foreach($states as $state){
                //         $state_string[] = $state->state_name;
                //     }
                //     return implode(",", $state_string);
                // })

                // ->editColumn('status', function ($row) {
                //     $status = '<a href="javascript:void(0);" class="badge badge-light-'.(($row->status == 'published') ? 'success': 'danger').'" tooltip="Click to '.(($row->status == 'published') ? 'Unpublish' : 'Publish').'" onclick="return commonChangeStatus(' . $row->id . ', \''.(($row->status == 'published') ? 'unpublished': 'published').'\', \'product-collection\')">'.ucfirst($row->status).'</a>';
                //     return $status;
                // })
                ->addColumn('no_of_states', function ($row) {
                    return count($row->collectionStates);
                })
                ->addColumn('action', function ($row) {
                    $edit_btn = '<a href="javascript:void(0);" onclick="return  openForm(\'zone\',' . $row->id . ')" class="btn btn-icon btn-active-primary btn-light-primary mx-1 w-30px h-30px" >
                                    <i class="fa fa-edit"></i>
                                </a>';
                    $del_btn = '<a href="javascript:void(0);" onclick="return commonDelete(' . $row->id . ', \'zone\')" class="btn btn-icon btn-active-danger btn-light-danger mx-1 w-30px h-30px" >
                                <i class="fa fa-trash"></i></a>';
                    return $edit_btn.$del_btn;
                })
                ->rawColumns(['action','state', 'area']);
            return $datatables->make(true);
        }
        return view('platform.zone.index', compact('title','breadCrum'));
    }

    public function modalAddEdit(Request $request)
    {

        $title              = "Add Zone";
        $breadCrum          = array('Zones', 'Add Zone');

        $id                 = $request->id;
        $from               = $request->from;
        $info               = '';
        $modal_title        = 'Add Zone';
        // $areas = Area::get();
        // $pincodes = Pincode::get();
        $states = State::where('status', 1)->get();
        // $pincodes           = Pincode::where('status', 'published')
        //                         ->when($id != '', function($q) use($id){
        //                             $q->whereRaw('id not IN(SELECT product_id FROM `mm_product_collections_products` where product_collection_id  != '.$id.')');
        //                         } )
        //                         ->when($id == '', function($q){
        //                             $q->whereRaw('id not IN(SELECT product_id FROM `mm_product_collections_products`)');
        //                         } )
        //                         ->where('stock_status', 'in_stock')
        //                         ->get();

        // $productCategory    = ProductCollection::where('status', 'published')->get();

        if (isset($id) && !empty($id)) {
            $info           = Zone::find($id);//Zone::select('id','zone_id','zone_name','zone_order', 'status')->selectRaw('GROUP_CONCAT(state) as states')->where('zone_id', $id)->groupBy('zone_id')->first();
            // $info['state'] = explode(',', $info->states);
            // $info['state_name'] = State::select('state_name')->where('id', $info->state_id)->first();
            // $info->area_name = Area::select('area_name')->where([['id', $info->area_id],['state_id', $info->state_id]])->value('area_name');
            // $info['pincode'] = Pincode::select('area_name')->where([['area_id', $info->area_id],['state_id', $info->state_id]])->first();
            $modal_title    = 'Update Zone';
        }



        return view('platform.zone.add_edit_modal', compact('modal_title', 'breadCrum', 'info', 'states'));
    }

    public function saveForm(Request $request,$id = null)
    {
        $id             = $request->id;
        $validator      = Validator::make($request->all(), [
                            'zone_name' => 'required|string|unique:product_collections,collection_name,' . $id,
                            'state' => 'required|array',
                        ]);
        $collection_id  = '';
        if ($validator->passes()) {
            $error                      = 0;
            $ins['zone_name']     = $request->zone_name;
            $ins['zone_order'] = $request->zone_order;
            if($request->status)
            {
                $ins['status']          = 'published';
            } else {
                $ins['status']          = 'unpublished';
            }
            $collectionInfo             = Zone::updateOrCreate(['id' => $id], $ins);
            $collection_id              = $collectionInfo->id;
            if( isset($request->state) && !empty($request->state) ) {
                ZoneState::where('zone_id', $collection_id)->delete();
                $iteration              = 1;
                foreach ( $request->state as $states ) {

                    $insstate['state_id']            = $states;
                    $insstate['zone_id'] = $collection_id;
                    $collectionInfo             = ZoneState::create($insstate);

                    $iteration++;
                }
            }



            $error                      = 0;

            // $collectionInfo->save();


            $message                    = (isset($id) && !empty($id)) ? 'Updated Successfully' : 'Added successfully';
        } else {
            $error      = 1;
            $message    = $validator->errors()->all();
        }
        return response()->json(['error' => $error, 'message' => $message, 'collection_id' => $id, 'from' => $request->from ?? '']);
    }

    public function delete(Request $request)
    {
        $id         = $request->id;
        $info       = Zone::find($id);
        $info->forceDelete();
        return response()->json(['message'=>"Successfully deleted zone!",'status'=>1]);
    }

    public function changeStatus(Request $request)
    {

        $id             = $request->id;
        $status         = $request->status;
        $info           = Zone::find($id);
        $info->status   = $status;
        $info->update();

        return response()->json(['message'=>"You changed the status!",'status'=>1]);

    }

    public function export()
    {
        return Excel::download(new ProductCollectionExport, 'prodcutCollections.xlsx');
    }

    public function exportPdf()
    {
        $list       = ProductCollection::all();
        $pdf        = PDF::loadView('platform.exports.product.product_collection_excel', array('list' => $list, 'from' => 'pdf'))->setPaper('a4', 'landscape');;
        return $pdf->download('productCollections.pdf');
    }

    public function getAreas($state_id = 0){
        $area['data'] = Area::where('state_id', $state_id)->get();
        return response()->json($area);
    }

    public function getPincodes($area_id = 0){
        $pincode['data'] =  Pincode::where('area_id', $area_id)->get();
        return response()->json($pincode);
    }

}
