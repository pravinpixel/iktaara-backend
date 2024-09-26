<?php

namespace App\Http\Controllers;

use App\Models\Banner;
use Illuminate\Http\Request;
use App\Exports\BannerExport;
use Illuminate\Support\Facades\Validator;
use DataTables;
use Carbon\Carbon;
use Excel;
use Illuminate\Support\Facades\Storage;
use PDF;
use Image;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class BannerController extends Controller
{
    public function index(Request $request)
    {
        $title = "Banner";
        if ($request->ajax()) {
            $data = Banner::select('banners.*', 'users.name as users_name')->join('users', 'users.id', '=', 'banners.added_by');
            $status = $request->get('status');
            $keywords = $request->get('search')['value'];
            $datatables =  Datatables::of($data)
                ->filter(function ($query) use ($keywords, $status) {
                    if ($status) {
                        return $query->where('banners.status', '=', "$status");
                    }
                    if ($keywords) {
                        $date = date('Y-m-d', strtotime($keywords));
                        return $query->where('banners.title', 'like', "%{$keywords}%")->orWhere('banners.status', 'like', "%{$keywords}%")->orWhere('users.name', 'like', "%{$keywords}%")->orWhere('banners.description', 'like', "%{$keywords}%")->orWhere('banners.tag_line', 'like', "%{$keywords}%")->orWhereDate("banners.created_at", $date);
                    }
                })
                ->addIndexColumn()

                ->editColumn('status', function ($row) {
                    $status = '<a href="javascript:void(0);" class="badge badge-light-' . (($row->status == 'published') ? 'success' : 'danger') . '" tooltip="Click to ' . (($row->status == 'published') ? 'Unpublish' : 'Publish') . '" onclick="return commonChangeStatus(' . $row->id . ',\'' . (($row->status == 'published') ? 'unpublished' : 'published') . '\', \'banner\')">' . ucfirst($row->status) . '</a>';
                    return $status;
                })
                ->editColumn('banner_type', function ($row) {
                    $banner_type = '';
                    if ($row->banner_type == 'main_home') {
                        $banner_type = "Main Home Page";
                    } elseif ($row->banner_type == 'ecom_home') {
                        $banner_type = "Ecommerce Home Page";
                    } elseif ($row->banner_type == 'login') {
                        $banner_type = "Login Page";
                    } elseif ($row->banner_type == 'promo_home') {
                        $banner_type = "Main Home Page Promo banner";
                    } elseif ($row->banner_type == 'ecom_promo_home') {
                        $banner_type = "Ecommerce Home Promo banner";
                    } elseif ($row->banner_type == 'brand_category') {
                        $banner_type = "Brand and Category Link Banner - Main ";
                    } elseif ($row->banner_type == 'home_category_section') {
                        $banner_type = "Home Cirlce Category Section - Ecommerce ";
                    }
                    return $banner_type;
                })
                ->editColumn('image', function ($row) {
                    if ($row->banner_image) {

                        $bannerImagePath = 'banner/' . $row->id . '/main_banner/' . $row->banner_image;
                        $url = Storage::url($bannerImagePath);
                        $path = asset($url);
                        $banner_image = '<div class="symbol symbol-45px me-5"><img src="' . $path . '" alt="" /><div>';
                    } else {
                        $path = asset('userImage/no_Image.png');
                        $banner_image = '<div class="symbol symbol-45px me-5"><img src="' . $path . '" alt="" /><div>';
                    }
                    return $banner_image;
                })

                ->editColumn('created_at', function ($row) {
                    $created_at = Carbon::createFromFormat('Y-m-d H:i:s', $row['created_at'])->format('d-m-Y');
                    return $created_at;
                })

                ->addColumn('action', function ($row) {
                    $edit_btn = '<a href="javascript:void(0);" onclick="return  openForm(\'banner\',' . $row->id . ')" class="btn btn-icon btn-active-primary btn-light-primary mx-1 w-30px h-30px" >
                    <i class="fa fa-edit"></i>
                </a>';
                    $del_btn = '<a href="javascript:void(0);" onclick="return commonDelete(' . $row->id . ', \'banner\')" class="btn btn-icon btn-active-danger btn-light-danger mx-1 w-30px h-30px" >
                <i class="fa fa-trash"></i></a>';

                    return $edit_btn . $del_btn;
                })
                ->rawColumns(['action', 'status', 'image', 'banner_type']);
            return $datatables->make(true);
        }
        $breadCrum  = array('Banner');
        $title      = 'Banner';
        return view('platform.banner.index', compact('breadCrum', 'title'));
    }

    public function modalAddEdit(Request $request)
    {

        $id                 = $request->id;
        $from               = $request->from;
        $info               = '';
        $modal_title        = 'Add Banner';
        if (isset($id) && !empty($id)) {
            $info           = Banner::find($id);
            $modal_title    = 'Update Banner';
        }
        $banner_types = ['main_home' => 'Main Home page banner', 'ecom_home' => 'Ecommerce Home page', 'login' => 'Login page', 'promo_home' => 'Promo banner home page', 'ecom_promo_home' => 'Ecom promo home page', 'brand_category' => 'Brand and Category - Main', 'home_category_section' => 'Home Circle Category Section - Ecommerce'];

        return view('platform.banner.add_edit_modal', compact('info', 'modal_title', 'from', 'banner_types'));
    }

    public function saveForm(Request $request, $id = null)
    {
        $id             = $request->id;
        $bannerType = $request->input('banner_type');
        $bannerType = $request->input('banner_type');

        // Base validation rules
        $rules = [
            'title' => [
                'required',
                Rule::unique('banners')->where(function ($query) use ($request) {
                    return $query->where('banner_type', $request->banner_type)->whereNull('deleted_at');
                })->ignore($id),
            ],
            'avatar' => 'mimes:jpeg,png,jpg|max:1024',
            'order_by' => 'required',
            // 'banner_image' => 'required|image|mimes:jpeg,png,jpg|max:2048', // Basic validation
        ];

        $messages = [
            'avatar.max' => 'The banner image may not be greater than 1MB.',
            'avatar.dimensions' => [
                'main_home' => 'The banner image must be at least 1920x420 pixels.',
                'ecom_home' => 'The banner image must be at least 1920x420 pixels.',
                'home_category_section' => 'The banner image must be at least 110x110 pixels.',
                'promo_home' => 'The banner image must be at least 1138x344 pixels.',
                'ecom_promo_home' => 'The banner image must be at least 1138x344 pixels.',
                'login' => 'The banner image must be at least 274x528 pixels.',
            ][$bannerType] ?? 'The banner image dimensions are invalid.',
        ];

        // Apply dimensions based on banner_type
        switch ($bannerType) {
            case 'main_home':
            case 'ecom_home':
                $rules['avatar'] .= '|dimensions:min_width=1920,min_height=420';
                break;
            case 'home_category_section':
                $rules['avatar'] .= '|dimensions:min_width=110,min_height=110';
                break;
            case 'promo_home':
                $rules['avatar'] .= '|dimensions:min_width=1138,min_height=344';
                break;
            case 'ecom_promo_home':
                $rules['avatar'] .= '|dimensions:min_width=1138,min_height=344';
                break;
            default:
                // Optionally handle cases where the banner_type is not recognized
                break;
        }

        // Create the validator instance
        $validator = Validator::make($request->all(), $rules, $messages);

        if ($validator->passes()) {

            if ($request->image_remove_image == "yes") {
                $ins['banner_image'] = '';
            }

            $ins['title']               = $request->title;
            $ins['description']         = $request->description;
            $ins['tag_line']            = $request->tag_line;
            $ins['links']               = $request->links;
            $ins['order_by']            = $request->order_by ?? 0;
            $ins['added_by']            = auth()->user()->id;
            $ins['banner_type']               = $request->banner_type;

            if ($request->status == "1") {
                $ins['status']          = 'published';
            } else {
                $ins['status']          = 'unpublished';
            }
            $error                      = 0;
            $info                       = Banner::updateOrCreate(['id' => $id], $ins);
            $banner_id                  = $info->id;


            $banner_info = Banner::find($banner_id);
            if ($request->image_remove_image == "yes") {
                $directory = 'banner/' . $banner_id;
                Storage::deleteDirectory('public/' . $directory);
            }

            if ($request->hasFile('avatar')) {

                $directory = 'banner/' . $banner_id . '/main_banner';
                Storage::deleteDirectory('public/' . $directory);

                $file                   = $request->file('avatar');
                $imageName              = uniqid() . Str::replace(' ', '-', $file->getClientOriginalName());
                if (!is_dir(storage_path("app/public/banner/" . $banner_id . "/main_banner"))) {
                    mkdir(storage_path("app/public/banner/" . $banner_id . "/main_banner"), 0775, true);
                }
                if (!is_dir(storage_path("app/public/banner/" . $banner_id . "/other_banner"))) {
                    mkdir(storage_path("app/public/banner/" . $banner_id . "/other_banner"), 0775, true);
                }

                $mainBanner            = 'public/banner/' . $banner_id . '/main_banner/' . $imageName;
                Image::make($file)->save(storage_path('app/' . $mainBanner));

                $otherBanner            = 'public/banner/' . $banner_id . "/other_banner/" . $imageName;
                Image::make($file)->resize(1920, 420)->save(storage_path('app/' . $otherBanner));

                $banner_info->banner_image       = $imageName;
                $banner_info->update();
            }

            if ($request->hasFile('banner')) {

                $directory = 'banner/' . $banner_id . '/mobile_banner';
                Storage::deleteDirectory('public/' . $directory);

                $file1                   = $request->file('banner');

                $imageName1              = uniqid() . Str::replace(' ', "-", $file1->getClientOriginalName());

                if (!is_dir(storage_path("app/public/banner/" . $banner_id . "/mobile_banner"))) {
                    mkdir(storage_path("app/public/banner/" . $banner_id . "/mobile_banner"), 0775, true);
                }

                $mobileBanner            = 'public/banner/' . $banner_id . "/mobile_banner/" . $imageName1;
                Image::make($file1)->save(storage_path('app/' . $mobileBanner));
                $banner_info->mobile_banner = $imageName1;
                $banner_info->update();
            }
            $message                    = (isset($id) && !empty($id)) ? 'Updated Successfully' : 'Added successfully';
        } else {
            $banner_id = $id;
            $error                      = 1;
            $message                    = $validator->errors()->all();
        }
        return response()->json(['error' => $error, 'message' => $message, 'banner_id' => $banner_id]);
    }

    public function changeStatus(Request $request)
    {
        $id             = $request->id;
        $status         = $request->status;
        $info           = Banner::find($id);
        $info->status   = $status;
        $info->update();
        return response()->json(['message' => "You changed the Banner status!", 'status' => 1]);
    }

    public function delete(Request $request)
    {
        $id         = $request->id;
        $info       = Banner::find($id);
        $info->delete();
        $directory = 'banner/' . $id;
        Storage::deleteDirectory('public/' . $directory);
        return response()->json(['message' => "Successfully deleted Banner!", 'status' => 1]);
    }

    public function export()
    {
        return Excel::download(new BannerExport, 'banner.xlsx');
    }

    public function exportPdf()
    {
        $list       = Banner::select('banners.*', 'users.name as users_name')->join('users', 'users.id', '=', 'banners.added_by')->get();
        $pdf        = PDF::loadView('platform.exports.banner.excel', array('list' => $list, 'from' => 'pdf'))->setPaper('a4', 'landscape');
        return $pdf->download('banner.pdf');
    }
}
