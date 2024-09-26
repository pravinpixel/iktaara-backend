<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\FooterContent;
use DataTables;
use Carbon\Carbon;
use Excel;
use Illuminate\Support\Facades\Storage;
use PDF;
use Image;
use Validator;
use Illuminate\Support\Str;
class FooterController extends Controller
{
    public function index(Request $request)
    {   //dd($request->search);
        $title = "Footer";
        if ($request->ajax()) {
            $data = FooterContent::select('footer_contents.*');
            //$status = $request->get('status');
            $keywords = $request->get('search')['value'];
            $datatables =  Datatables::of($data)
                ->filter(function ($query) use ($keywords) {
                    // if ($status) {
                        //return $query->where('footer_contents.enabled', '=', 1);
                    // }
                    if ($keywords) {
                        //$date = date('Y-m-d', strtotime($keywords));
                        return $query->where('footer_contents.name', 'like', "%{$keywords}%")->orWhere('footer_contents.slug', 'like', "%{$keywords}%")->orWhere('footer_contents.description', 'like', "%{$keywords}%");
                    }
                })
                ->addIndexColumn()

                // ->editColumn('enabled', function ($row) {
                //     $enabled = ($row->enabled == 0) ? 'In Active' : 'Active';
                //     return $enabled;
                // })
                // ->editColumn('image', function ($row) {
                //     if ($row->banner_image) {

                //         $bannerImagePath = 'banner/'.$row->id.'/main_banner/'.$row->banner_image;
                //         $url = Storage::url($bannerImagePath);
                //         $path = asset($url);
                //         $banner_image = '<div class="symbol symbol-45px me-5"><img src="' . $path . '" alt="" /><div>';
                //     } else {
                //         $path = asset('userImage/no_Image.png');
                //         $banner_image = '<div class="symbol symbol-45px me-5"><img src="' . $path . '" alt="" /><div>';
                //     }
                //     return $banner_image;
                // })

                // ->editColumn('created_at', function ($row) {
                //     $created_at = Carbon::createFromFormat('Y-m-d H:i:s', $row['created_at'])->format('d-m-Y');
                //     return $created_at;
                // })

                ->addColumn('action', function ($row) {
                    $edit_btn = '<a href="javascript:void(0);" onclick="return  openForm(\'footers\',' . $row->id . ')" class="btn btn-icon btn-active-primary btn-light-primary mx-1 w-30px h-30px" >
                    <i class="fa fa-edit"></i>
                </a>';
                //     $del_btn = '<a href="javascript:void(0);" onclick="return commonDelete(' . $row->id . ', \'topbar\')" class="btn btn-icon btn-active-danger btn-light-danger mx-1 w-30px h-30px" >
                // <i class="fa fa-trash"></i></a>';

                    return $edit_btn;
                })
                ->rawColumns(['action', 'status', 'image']);
            return $datatables->make(true);
        }
        $breadCrum  = array( 'Footer');
        $title      = 'Footer';
        return view('platform.footer.index', compact('breadCrum', 'title'));
    }

    public function modalAddEdit(Request $request)
    {

        $id                 = $request->id;
        $from               = $request->from;
        $info               = '';
        $modal_title        = 'Add Footer Content';
        if (isset($id) && !empty($id)) {
            $info           = FooterContent::find($id);
            $modal_title    = 'Update Footer Content';
        }

        return view('platform.footer.add_edit_modal', compact('info', 'modal_title', 'from'));

    }

    public function saveForm(Request $request,$id = null)
    {   
        //dd($request);
        $id             = $request->id;
        $validator      = Validator::make($request->all(), [
                                'name' => 'required|string',
                                'slug' => 'required|string',
                                'description' => 'required|string',
                            ]);
        $banner_id      = '';

        if ($validator->passes()) {

            $ins['name']               = $request->name;
            $ins['slug']         = $request->slug;
            $ins['description']         = $request->description;

            $error                      = 0;
            $info                       = FooterContent::updateOrCreate(['id' => $id], $ins);
            $banner_id                  = $info->id;

            $message                    = (isset($id) && !empty($id)) ? 'Updated Successfully' : 'Added successfully';
        } else {
            $error                      = 1;
            $message                    = $validator->errors()->all();
        }
        return response()->json(['error' => $error, 'message' => $message, 'banner_id' => $banner_id]);
    }

    public function changeStatus(Request $request)
    {
        $id             = $request->id;
        $status         = $request->status;
        $info           = FooterContent::find($id);
        $info->status   = $status;
        $info->update();
        return response()->json(['message'=>"You changed the FooterContent status!",'status'=>1]);

    }

    public function delete(Request $request)
    {
        $id         = $request->id;
        $info       = FooterContent::find($id);
        $info->delete();
        $directory = 'footer/'.$id;
        Storage::deleteDirectory('public/'.$directory);
        return response()->json(['message'=>"Successfully deleted Footer!",'status'=>1]);
    }
}
