<?php

namespace App\Http\Controllers;

// use App\Mail\CustomerRequestMail;
use App\Models\Category\MainCategory;
use App\Models\Master\Brands;
use App\Models\Master\State;
use App\Models\MerchantProduct;
use App\Models\Product\Product;
use App\Models\Product\ProductCategory;
use App\Models\Seller\Merchant;
use App\Models\Seller\MerchantShopsData;
use App\Models\Zone;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\File;
use Mail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use DataTables;
use Illuminate\Support\Facades\Storage;
use App\Models\Seller\Area;
use App\Models\Seller\Pincode;
use App\Consts;
use App\Exports\BannerExport;
use App\Exports\MerchantExport;
use App\Exports\MerchantOrderExport;
use App\Mail\DynamicMail;
use App\Models\GlobalSettings;
use App\Models\Master\EmailTemplate;
use App\Models\Master\OrderStatus;
use App\Models\MerchantOrder;
use App\Models\MerchantOrderStatus;
use App\Models\Order;
use App\Models\OrderProduct;
use App\Models\Seller\MerchantProfit;
use App\Models\Seller\MerchantStaturatoryData;
use Illuminate\Support\Facades\Hash;
use Excel;

class MerchantController extends Controller
{
    public function createForm(Request $request){

        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'email' => 'required|email|unique:customer_request_details',
            'mobile_no' => 'required|numeric|digits:10|unique:customer_request_details',
            'location' => 'required',
            'customer_category' => 'required',
            'pincode' => 'required|numeric'
        ]);

        if($validator->passes()){
            try{
                return DB::transaction(function() use ($request) {
                    $data['name'] = $request->name;
                    $data['email'] = $request->email;
                    $data['mobile_no'] = $request->mobile_no;
                    $data['location'] = $request->location;
                    $data['pincode'] = $request->pincode;
                    $data['customer_categories'] = $request->customer_category;
                    $data['customer_designation'] = $request->customer_designation;
                    $data['is_agree'] = $request->is_agree;
                    $data['desc'] = $request->desc;
                    $result = Merchant::create($data);
                    if(!empty($result)){

                        $send_mail = new Merchant($result, "Customer request");
                        // return $send_mail->render();
                        // Mail::to("abhinav@iktaraa.com")->send($send_mail);
                        return response()->json(['error' => 0, 'message' => "Customer created successfully"]);
                    }else{
                        return response()->json(['error' => 1, 'message' => "Customer created failed"]);
                    }

                });
            }catch (\Exception $e) {
                $error = 1;
                return response()->json(['error' => 1, 'message' => $e->getMessage()]);
            }
        }else{
            $error      = 1;
            $message    = $validator->errors()->all();
            return response()->json(['error' => $error, 'message' => $message]);
        }
    }

    public function index(Request $request)
    {

        $title = "Merchant List";
        if ($request->ajax()) {
            $data = Merchant::with('state:id,state_name', 'area:id,area_name', 'pincode:id,pincode')
                                ->select('id', 'merchant_no', 'priority', DB::raw("CONCAT(COALESCE(first_name,''), ' ', COALESCE(last_name,'')) as name"), 'email', 'mobile_no', 'address', 'state_id', 'zone_id','terms_conditions', 'status')->orderBy('id', 'desc');
                                ;
            $status = ($request->get('status') == "all") ? '' : $request->get('status');
            $zone_filter = ($request->get('zone') == "all") ? '' : $request->get('zone');
            $keywords = $request->get('search')['value'];
            $datatables =  Datatables::of($data)
                ->filter(function ($query) use ($status, $keywords, $zone_filter) {
                    if(!empty($status) && !empty($zone_filter)){
                        return $query->where('status', '=', $status)->where('zone_id', '=', $zone_filter);
                    }
                    if (!empty($status)) {
                        return $query->where('status', '=', $status);
                    }
                    if (!empty($zone_filter)) {
                        return $query->where('zone_id', '=', $zone_filter);
                    }
                    if ($keywords) {
                        return $query->where('merchants.first_name', 'like', "%{$keywords}%")->orWhere('merchants.last_name', 'like', "%{$keywords}%")->orWhere('merchants.status', 'like', "%{$keywords}%")->orWhere('merchants.merchant_no', 'like', "%{$keywords}%")->orWhere('merchants.mobile_no', 'like', "%{$keywords}%")->orWhere('merchants.email', 'like', "%{$keywords}%");
                    }
                })
                ->addIndexColumn()
                ->editColumn('terms_conditions', function ($row) {
                    $status = '<a href="javascript:void(0);" class="badge badge-light-'.(($row->terms_conditions == "on") ? 'success': 'danger').'" tooltip="Click to '.(($row->terms_conditions == 'on') ? 'Accepted' : 'Not Accepted').'" onclick="return commonChangeStatus(' . $row->id . ',\''.(($row->terms_conditions == "on") ? 'Accepted': 'Not Accepted').'\', \'merchants\')">'.($row->terms_conditions == 'on') ? 'Accepted': 'Not Accepted'.'</a>';
                    return $status;
                })
                ->editColumn('merchant_info', function ($row) {
                    $info = '';
                    $info .= '<div>'.$row->name.'</div>';
                    $info .= '<div>'.$row->email.'</div>';
                    $info .= '<div>'.$row->mobile_no.'</div>';
                    return $info;
                })
                ->editColumn('status', function ($row) {
                    return ucwords($row->status);
                })
                ->editColumn('zone_name', function ($row) {
                    $zone = Zone::where('id', $row->zone_id)->where('status', 'published')->first();
                    return $zone->zone_name ?? '';
                })
                ->editColumn('view_products', function ($row) {
                    $view_product_route = "merchants/view/products/$row->id";
                    $view_product_btn = "<a href=".$view_product_route." class='btn btn-light btn-hover-primary font-weight-bold' style='font-size: 10px;padding: 8px;'>View Products</a>";

                    return $view_product_btn;
                })
                ->editColumn('view_orders', function ($row) {
                    $view_order_route = "merchants/view/orders/$row->id";
                    $view_order_btn = "<a href=".$view_order_route." class='btn btn-light btn-hover-primary font-weight-bold' style='font-size: 10px;padding: 8px;'>View Orders</a>";

                    return $view_order_btn;
                })

                ->addColumn('action', function ($row) {
                    $edit_btn = '<a href="javascript:void(0);" onclick="return  openForm(\'merchants\',' . $row->id . ')" class="btn btn-icon btn-active-primary btn-light-primary mx-1 w-30px h-30px" >
                    <i class="fa fa-edit"></i>
                </a>';
                    $del_btn = '<a href="javascript:void(0);" onclick="return commonDelete(' . $row->id . ', \'merchants\')" class="btn btn-icon btn-active-danger btn-light-danger mx-1 w-30px h-30px" >
                <i class="fa fa-trash"></i></a>';



                    return $edit_btn . $del_btn;
                })
                ->rawColumns(['action', 'status', 'view_products', 'view_orders', 'zone_name', 'merchant_info']);
            return $datatables->make(true);
        }
        $breadCrum  = array( 'Merchant List');
        $title      = 'Merchant List';
        $zones = Zone::get();
        return view('platform.merchants.merchants-list', compact('breadCrum', 'title', 'zones'));
    }

    public function modalAddEdit(Request $request, $id = null)
    {

        $id = $request->id;
        $from = $request->from;
        $info  = '';
        $merchantViewData = $merchantAreas = $merchantPincodes = $merchantShopData = $merchantStaturatoryData = '';
        $merchantProfitCategoryData = $merchantProfitBrandData = $merchantShopAreas = $merchantShopPincodes = '';
        $modal_title = 'Add Merchant';
        $states = State::where('status', 1)->get();
        $categories = ProductCategory::where('status', 'published')->where('parent_id', 0)->get();
        $brands = Brands::where('status', 'published')->get();
        if (isset($id) && !empty($id)) {
            $merchantViewData = Merchant::find($id);
            $merchantAreas = filterArea($merchantViewData->state_id);
            $merchantPincodes = filterPincode($merchantViewData->state_id, $merchantViewData->area_id);
            $merchantShopData = MerchantShopsData::where('merchant_id', $id)->first();
            if(isset($merchantShopData->state_id)){
                $merchantShopAreas = filterArea($merchantShopData->state_id);
                $merchantShopPincodes = filterPincode($merchantShopData->state_id, $merchantShopData->area_id);
            }
            $merchantStaturatoryData = MerchantStaturatoryData::where('merchant_id', $id)->first();
            $merchantProfitBrandData = MerchantProfit::where('merchant_id', $id)->whereNotNull('brand_id')->get();
            // dd($merchantProfitBrandData->pluck('brand_margin_value', 'brand_id'));
            $merchantProfitCategoryData = MerchantProfit::where('merchant_id', $id)->whereNotNull('category_id')->get();
            $modal_title = 'Update Merchant';
        }
        return view( 'platform.merchants.add_edit_modal',
                      compact( 'id',
                                'modal_title',
                                'merchantViewData',
                                'merchantAreas',
                                'merchantPincodes',
                                'from',
                                'states',
                                'categories',
                                'brands',
                                'merchantShopData',
                                'merchantShopAreas',
                                'merchantShopPincodes',
                                'merchantStaturatoryData',
                                'merchantProfitCategoryData',
                                'merchantProfitBrandData' )
                    );
        // return view('platform.merchants.add_edit_modal', compact('modal_title', 'merchant_viewData', 'state', 'area', 'pincode'));

    }
    public function dataSaveEditForm(Request $request, $id = null){
        
        if($request->from === Consts::CONTACT_FORM){
           return $this->contactForm($request, $id);

        }elseif($request->from === Consts::SELLER_FORM){
            return $this->sellerForm($request, $id);

        }elseif($request->from === Consts::STATURATORY_FORM ){
            return $this->staturatoryForm($request, $id);

        }elseif($request->from === Consts::PROFIT_FORM){
            return $this->profitForm($request, $id);

        }elseif($request->from === Consts::PRIORITY_FORM){
            return $this->priorityForm($request, $id);
        }
    }

    private function priorityForm(Request $request, $id){

        if($request->has('status')){
            $merchant = Merchant::find($id);
            $merchant->status = Consts::APPROVED;
            $merchant->priority = $request->priority;
            $merchant->mode = $request->mode;
            $merchant->save();
            if(!$merchant->is_approved_email_sent){
                $merchant = Merchant::find($id);
                $merchant->is_approved_email_sent = true;
                $merchant->save();
                /** send email for new customer */
                $emailTemplate = EmailTemplate::select('email_templates.*')
                ->join('sub_categories', 'sub_categories.id', '=', 'email_templates.type_id')
                ->where('sub_categories.slug', 'merchant-approved')->first();
                $globalInfo = GlobalSettings::first();

            // $link = 'http://192.168.0.35:3000/#/verify-account/' . $token_id;

                $extract = [
                    'name' => $merchant->firstName. ' ' .$merchant->lastName,
                    'regards' => $globalInfo->site_name,
                    'company_website' => $globalInfo->site_name,
                    'company_mobile_no' => $globalInfo->site_mobile_no,
                    'company_address' => $globalInfo->address,
                    'user_name' => $merchant->email,
                ];

                $templateMessage = $emailTemplate->message;
                $templateMessage = str_replace("{", "", addslashes($templateMessage));
                $templateMessage = str_replace("}", "", $templateMessage);
                extract($extract);
                eval("\$templateMessage = \"$templateMessage\";");

                $send_mail = new DynamicMail($templateMessage, $emailTemplate->title);
                //  return $send_mail->render();
                sendEmailWithBcc($merchant->email, $send_mail);
            }

            return $this->responseSuccess("Merchant Saved Successfully");
        }else{
            $merchant = Merchant::find($id);
            $merchant->status = Consts::NON_APPROVED;
            $merchant->priority = $request->priority;
            $merchant->save();
            return $this->responseSuccess("Merchant Not Approved");
        }


    }

    private function profitForm($request, $id){

        $filterBrandValues = array_filter($request->brand_margin_value, function ($value) {
            return $value !== null;
        });

        $filterCategoryValues = array_filter($request->category_margin_value, function ($value) {
            return $value !== null;
        });

        if (sizeof($filterBrandValues) > 0 ) {
            MerchantProfit::where('merchant_id',$id)->delete();
            foreach( $filterBrandValues as $brand_id => $filterBrandValue ){
                $data_brand['brand_id'] = $brand_id;
                $data_brand['brand_margin_value'] = $filterBrandValue;
                $data_brand['merchant_id'] = $id;
                $brandResult = MerchantProfit::Create($data_brand);
            }
        }
        if (sizeof($filterCategoryValues) > 0) {

            foreach( $filterCategoryValues  as $category_id => $filterCategoryValue ){
                $data['category_id'] = $category_id;
                $data['category_margin_value'] = $filterCategoryValue;
                $data['merchant_id'] = $id;
                $categoryResult = MerchantProfit::Create($data);
            }

        }
        if(isset($brandResult) && !empty($brandResult) || isset($categoryResult) && !empty($categoryResult)){

            return $this->responseSuccess("Profit Margin Saved");
        }else{

            return $this->responseError("Something went wrong");
        }
    }

    private function staturatoryForm(Request $request, $id){

        if(isset($id) && !empty($id)){

            $validator = Validator::make($request->all(), [
                'gst_no' => 'required|string|unique:merchant_staturatory_data,gst_no,'.$id.',merchant_id',
                'pan_no' => 'required|string|unique:merchant_staturatory_data,pan_no,'.$id.',merchant_id',
                'gst_document.*'=> 'mimes:jpeg,bmp,png,pdf,xlsx,csv,doc,docx,txt,xls|max:1000',
                'pan_document.*'=> 'mimes:jpeg,bmp,png,pdf,xlsx,csv,doc,docx,txt,xls|max:1000',
                'agree_document.*'=> 'mimes:jpeg,bmp,png,pdf,xlsx,csv,doc,docx,txt,xls|max:1000',

            ]);

        }else{

            $validator = Validator::make($request->all(),[
                'gst_no' => 'required|string|unique:merchant_staturatory_data,gst_no',
                'pan_no' => 'required|string|unique:merchant_staturatory_data,pan_no',
                'gst_document.*'=> 'mimes:jpeg,bmp,png,pdf,xlsx,csv,doc,docx,txt,xls|max:1000',
                'pan_document.*'=> 'mimes:jpeg,bmp,png,pdf,xlsx,csv,doc,docx,txt,xls|max:1000',
                'agree_document.*'=> 'mimes:jpeg,bmp,png,pdf,xlsx,csv,doc,docx,txt,xls|max:1000',
            ]);
        }

        if ($validator->passes()) {

            $data['gst_no'] = $request->gst_no;
            $data['pan_no'] = $request->pan_no;

            if ($request->hasFile('gst_document')) {

                $filename       = time() . '_' . $request->gst_document->getClientOriginalName();
                $directory      = 'merchants/'.$id.'/gst_document';
                $filename       = $directory.'/'.$filename;
                Storage::deleteDirectory('public/'.$directory);
                Storage::disk('public')->put($filename, File::get($request->gst_document));
                $publicUrl = url('storage/' . $filename);
                $data['gst_document'] = $publicUrl;
            }else{
                if(isset($request->gst_hidden_image_url) && $request->gst_remove_image === 'no'){
                    $data['gst_document'] = $request->gst_hidden_image_url;
                }else{
                    $data['gst_document'] = null;
                }
            }

            if ($request->hasFile('pan_document')) {

                $filename       = time() . '_' . $request->pan_document->getClientOriginalName();
                $directory      = 'merchants/'.$id.'/pan_document';
                $filename       = $directory.'/'.$filename;
                Storage::deleteDirectory('public/'.$directory);
                Storage::disk('public')->put($filename, File::get($request->pan_document));
                $publicUrl = url('storage/' . $filename);
                $data['pan_document'] = $publicUrl;
            }else{
                if(isset($request->pan_hidden_image_url) && $request->pan_remove_image === 'no'){
                    $data['pan_document'] = $request->pan_hidden_image_url;
                }else{
                    $data['pan_document'] = null;
                }
            }

            if ($request->hasFile('agree_document')) {

                $filename       = time() . '_' . $request->agree_document->getClientOriginalName();
                $directory      = 'merchants/'.$id.'/agree_document';
                $filename       = $directory.'/'.$filename;
                Storage::deleteDirectory('public/'.$directory);
                Storage::disk('public')->put($filename, File::get($request->agree_document));
                $publicUrl = url('storage/' . $filename);
                $data['agree_document'] = $publicUrl;
            }else{
                if(isset($request->agree_hidden_image_url) && $request->agree_remove_image === 'no'){
                    $data['agree_document'] = $request->agree_hidden_image_url;
                }else{
                    $data['agree_document'] = null;
                }
            }

            $staturatoryInfo = MerchantStaturatoryData::updateOrCreate(['merchant_id' => $id], $data);
            if(isset($staturatoryInfo) && !empty($staturatoryInfo)){

                return $this->responseSuccess("Staturatory Information Saved");
            }else{

                return $this->responseError("Something went wrong");
            }

        }else{

            return $this->responseError( $validator->errors()->all() );
        }

    }
    private function contactForm(Request $request, $id = null){

        /* Diff Type of field validations for update or create */

        if(isset($id) && !empty($id)){
            $validator = Validator::make($request->all(), [
                'first_name' => 'required|string',
                'email' => 'required|email|unique:merchants,email,'.$id.',id',
                'mobile_no' => 'required|min:10|numeric|unique:merchants,mobile_no,'.$id.',id',
            ]);


        }else{
            $validator = Validator::make($request->all(),[
                'first_name' => 'required',
                'email' => 'required|email|unique:merchants,email',
                'mobile_no' => 'required|min:10|numeric|unique:merchants,mobile_no',
            ]);
            $data['password'] = Hash::make($request->merchant_password);
            $data['merchant_no'] = getMerchantNo();
            $data['status'] = 'non_approved';
        }

        if ($validator->passes()) {

            $data['first_name'] = $request->first_name;
            $data['last_name'] = $request->last_name;
            $data['email'] = $request->email;
            $data['mobile_no'] = $request->mobile_no;
            $data['address'] = $request->address;
            $data['city'] = $request->city;
            $data['state_id'] = $request->state_id;
            $data['area_id'] = $request->area_id;
            $data['pincode_id'] = $request->pincode_id;
            $data['desc'] = $request->desc;
            $data['status'] = Consts::REGISTERED;

            /* Id is exists update if not create */

            $contactInfo = Merchant::updateOrCreate(['id' => $id], $data);
            if(isset($contactInfo) && !empty($contactInfo)){

                return $this->responseSuccess("Contact Information Saved");
            }else{

                return $this->responseError("Something went wrong");
            }
        }else {

            return $this->responseError( $validator->errors()->all() );
        }

    }


    private function sellerForm(Request $request, $id = null){

        if(isset($id) && !empty($id)){
            $validator = Validator::make($request->all(), [
                'shop_name' => 'required|string',
                'contact_person' => 'required|string',
                'contact_number' => 'required|min:10|numeric|unique:merchant_shops_data,contact_number,'.$id.',merchant_id',
                'state_id' => 'required'

            ]);

        }else{
            $validator = Validator::make($request->all(),[
                'shop_name' => 'required|string',
                'contact_person' => 'required|string',
                'contact_number' => 'required|min:10|numeric|unique:merchant_shops_data,contact_number',
                'state_id' => 'required'
            ]);

        }

        if ($validator->passes()) {

            $data['shop_name'] = $request->shop_name;
            $data['contact_person'] = $request->contact_person;
            $data['contact_number'] = $request->contact_number;
            $data['state_id'] = $request->state_id;
            $data['area_id'] = $request->area_id;
            $data['pincode_id'] = $request->pincode_id;
            if(isset($id)) {
                $data['merchant_id'] = $id;
                $merchant = Merchant::find($id);
                $merchant_zone_id = getZoneByStateId($request->state_id);
                $merchant->zone_id = $merchant_zone_id['id'];
                $merchant->update();
            }

            /* Id is exists update if not create */

            $sellerInfo = MerchantShopsData::updateOrCreate(['merchant_id' => $id], $data);

            if(isset($sellerInfo) && !empty($sellerInfo)){

                return $this->responseSuccess("Seller Location Saved");
            }else{

                return $this->responseError("Something went wrong");
            }
        }else {

            return $this->responseError( $validator->errors()->all() );
        }

    }

    public function delete(Request $request){
        $id         = $request->id;
        $info       = Merchant::find($id);
        $info->delete();
        $merchantInfo       = MerchantShopsData::where('merchant_id',$id)->get();
        $merchantInfo->delete();
        $directory      = 'merchants/'.$id;
        Storage::deleteDirectory($directory);
        return response()->json(['message'=>"Successfully deleted!",'status'=>1]);
    }

    public function viewMerchantProducts(Request $request, $id = null ){

        $title                  = "My Products";
        $breadCrum              = array('Products', 'Product');
        if ($request->ajax()) {
            $merchant_id = $request->get('merchant_id');
            $f_product_category = $request->get('filter_product_category');
            $f_brand = $request->get('filter_brand');
            $f_label = $request->get('filter_label');
            $f_tags = $request->get('filter_tags');
            $f_stock_status = $request->get('filter_stock_status');
            $f_product_name = $request->get('filter_product_name');
            $f_product_status = $request->get('filter_product_status');
            $f_video_booking = $request->get('filter_video_booking');

            $data = Product::leftJoin('brands', 'brands.id', '=', 'products.brand_id')->leftJoin('product_categories', 'product_categories.id', '=', 'products.category_id')
            ->leftJoin('merchant_products', 'merchant_products.product_id', '=', 'products.id')
            ->select('products.*', 'brands.brand_logo', 'brands.brand_name', 'product_categories.name as category', 'merchant_products.qty as merchant_quantity', 'merchant_products.product_id as merchant_product_id','merchant_products.id as merchant_product_order_id', 'merchant_products.status as merchant_product_status','merchant_products.low_stock_value as low_stock_value', 'merchant_products.merchant_id as merchant_id' )
            ->when($f_product_category, function ($q) use ($f_product_category) {
                    return $q->where('category_id', $f_product_category);
                })
                ->when($f_brand, function ($q) use ($f_brand) {
                    return $q->where('brands.id', $f_brand);
                })
                ->when($f_tags, function ($q) use ($f_tags) {
                    return $q->where('tag_id', $f_tags);
                })
                ->when($f_stock_status, function ($q) use ($f_stock_status) {
                    return $q->where('stock_status', $f_stock_status);
                })
                ->when($f_product_status, function ($q) use ($f_product_status) {
                    return $q->where('products.status', $f_product_status);
                })
                ->when($f_video_booking, function ($q) use ($f_video_booking) {
                    return $q->where('has_video_shopping', $f_video_booking);
                })
                ->when($f_product_name, function ($q) use ($f_product_name) {
                    return $q->where(function ($qr) use ($f_product_name) {
                        $qr->where('product_name', 'like', "%{$f_product_name}%")
                            ->orWhere('sku', 'like', "%{$f_product_name}%")
                            ->orWhere('mrp', 'like', "%{$f_product_name}%");
                    });
                })
                ->when($f_label, function ($q) use ($f_label) {
                    return $q->where('label_id', $f_label);
                })->where('merchant_products.merchant_id',$merchant_id)
                ;

            $keywords = $request->get('search')['value'];
            $datatables =  Datatables::of($data, $merchant_id)
                ->filter(function ($query) use ($keywords) {

                    if ($keywords) {
                        $date = date('Y-m-d', strtotime($keywords));
                        $query->where(function ($que) use ($keywords, $date) {
                            $que->where('has_video_shopping', 'like', "%{$keywords}%")
                                ->orWhere('products.status', 'like', "%{$keywords}%")
                                ->orWhere('products.stock_status', 'like', "%{$keywords}%")
                                ->orWhere('brands.brand_name', 'like', "%{$keywords}%")
                                ->orWhere('product_categories.name', 'like', "%{$keywords}%")
                                ->orWhere('products.product_name', 'like', "%{$keywords}%")
                                ->orWhere('products.sku', 'like', "%{$keywords}%")
                                ->orWhere('products.mrp', 'like', "%{$keywords}%")
                                ->orWhereDate("products.created_at", $date);
                        });
                        return $query;
                    }
                })
                ->addIndexColumn()
                ->editColumn('quantity', function ($row) {
                    // $quantity = '<div class="postion-relative">
                    // <div id="quantity_input_'.$row->id.'" class="quantity-label">'.$row->quantity.' <i class="fa fa-edit" role="button" onclick="changeStockQuantity('.$row->id.')"></i></div>
                    // <div class="form-group postion-absolute" id="quantity_edit_'.$row->id.'" style="display:none">
                    //     <input type="text" maxlength="3" value="'.$row->quantity.'" class="form-control w-90px numberonly" name="quantity" id="quantity_'.$row->id.'">
                    //     <i class="fa fa-check quantity-btn" onclick="quantityChange('.$row->id.')"></i>
                    //     <i class="fa fa-times quantity-close-btn" onclick="closeStockQuantity('.$row->id.')"></i>
                    // </div>
                    // </div>';
                    $quantity = $row->merchant_quantity;
                    return $quantity;
                })
                ->editColumn('status', function ($row) {
                    $status = ucfirst($row->merchant_product_status);
                    return $status;
                })
                ->editColumn('low_stock_value', function ($row) {
                    $low_stock_value = $row->low_stock_value;
                    return $low_stock_value;
                })
                ->editColumn('profit_margin', function ($row) {
                    $profit_margin = Merchant::getProfitMargin($row->merchant_product_id, $row->merchant_id, $row->mrp);
                    return $profit_margin ?? '';
                })
                ->editColumn('profit_margin_percent', function ($row) {
                    $profit_margin = Merchant::getProfitMarginPercentage($row->merchant_product_id, $row->merchant_id, $row->mrp);
                    return $profit_margin ?? '';
                })
                ->editColumn('brand', function ($row) {
                    return $row->productBrand->brand_name ?? '';
                })
                ->addColumn('action', function ($row) {
                    // if(MerchantProduct::getProductAssignedToMerchant($row->id)){
                    //     $assign_btn = '<a href="javascript:void(0);" onclick="return assignProduct(' . $row->id . ',' . $row->mrp . ')" class="btn btn-light btn-hover-primary font-weight-bold disabled " style="font-size: 12px;">Assigned</a>';

                    // }else{
                    //     $assign_btn = '<a href="javascript:void(0);" onclick="return assignProduct(' . $row->id . ',' . $row->mrp . ')" class="btn btn-light btn-hover-primary font-weight-bold " style="font-size: 12px;">Assign Product</a>';

                    // }
                    $edit_btn = '<a target="_blank" href="https://frontend.iktaraa.com/product/' . $row->product_url . '" class="btn btn-icon btn-active-primary btn-light-primary mx-1 w-30px h-30px" >
                                    <i class="fa fa-eye"></i>
                                </a>';
                        $del_btn = '<a href="javascript:void(0);" onclick="return commonUnassign(' . $row->merchant_product_order_id . ', \'merchant-products\')" class="btn btn-light-primary font-weight-bold mr-2" >
                    Unassign</a>';
                    return $edit_btn . $del_btn;
                })

                ->rawColumns(['action', 'status', 'category', 'quantity','low_stock_value']);


            return $datatables->make(true);

        }

        $productCategory        = ProductCategory::where('status', 'published')->get();
        $brands                 = Brands::where('status', 'published')->get();
        $productLabels          = MainCategory::where(['slug' => 'product-labels', 'status' => 'published'])->first();
        $productTags            = MainCategory::where(['slug' => 'product-tags', 'status' => 'published'])->first();
        $merchant_id = $id;
        $params                 = array(
            'title' => $title,
            'breadCrum' => $breadCrum,
            'merchant_id' => $id,
            'productCategory' => $productCategory,
            'brands' => $brands,
            'productLabels' => $productLabels,
            'productTags' => $productTags,
        );

        return view('platform.merchant_product.index', $params);
    }

    public function changeStatus(Request $request)
    {
        $id             = $request->id;
        $status         = $request->status;
        $info           = MerchantProduct::find($id);
        $info->status   = $status;
        $info->update();

        return response()->json(['message'=>"You changed the Product status!",'status' => 1 ] );

    }

    public function deleteMerchantProduct(Request $request)
    {
        $id         = $request->id;
        $info       = MerchantProduct::find($id);
        $info->delete();

        return response()->json(['message'=>"Successfully deleted Product!",'status' => 1 ] );
    }

    public function quantityChange(Request $request)
    {
        $id = $request->id;
        $value = $request->value;
        $merchant_id = $request->merchant_id;
        if (!empty($id) && !empty($value)) {
            $data = MerchantProduct::where([['product_id',$id], ['merchant_id',$merchant_id]])->first();
            if(!($data)){
                return response()->json(['message' => "Please assign the product and change the quantity", 'status' => 0]);
            }
            $data->qty = $value;
            $data->save();
            return response()->json(['message' => "You changed the Quantity value!", 'status' => 1]);
        }
    }

    public function lowStockChange(Request $request)
    {
        $id = $request->id;
        $value = $request->value;
        $merchant_id = $request->merchant_id;
        if (!empty($id) && !empty($value)) {
            $data = MerchantProduct::where([['product_id',$id], ['merchant_id',$merchant_id]])->first();
            if(!($data)){
                return response()->json(['message' => "Please assign the product and change the low stock value", 'status' => 0]);
            }
            $data->low_stock_value = $value;
            $data->save();
            return response()->json(['message' => "You changed the Low stock value!", 'status' => 1]);
        }
    }

    public function viewMerchantOrders(Request $request, $id = null ){

    {
        if ($request->ajax()) {
            $merchant_id = $request->get('merchant_id');
            $data = Order::selectRaw('mm_merchant_orders.total as product_amount, mm_payments.order_id,mm_payments.payment_no,mm_payments.status as payment_status,mm_orders.*, mm_products.product_name as product_name,mm_order_products.quantity as order_quantity, mm_merchant_orders.id as merchant_order_id, mm_merchant_orders.order_status as merchant_status, mm_merchant_orders.merchant_profit_margin as profit_margin_percentage, mm_merchant_orders.order_product_id as order_product_id')
            ->join('merchant_orders', 'merchant_orders.order_id', '=', 'orders.id')
                ->join('order_products', 'order_products.id', '=', 'merchant_orders.order_product_id')
                ->join('products', 'order_products.product_id', '=', 'products.id')
                ->join('payments', 'payments.order_id', '=', 'orders.id')

                ->where('merchant_orders.merchant_id', $merchant_id)
                ->groupBy('merchant_orders.order_product_id')->orderBy('orders.id', 'desc');
            $filter_subCategory   = '';
            $status = $request->get('status');
            $keywords = $request->get('search')['value'];
            $fromDate =  $request->get('fromDate');
            $toDate =  $request->get('toDate');
            $datatables =  DataTables::of($data)
            ->filter(function ($query) use ($keywords, $status, $fromDate, $toDate, $filter_subCategory) {
                if ($status) {
                    return $query->where('merchant_orders.order_status', 'like', $status);
                }
                if ($keywords) {
                    $date = date('Y-m-d', strtotime($keywords));
                    return $query->where('orders.billing_name', 'like', "%{$keywords}%")
                        ->orWhere('orders.billing_email', 'like', "%{$keywords}%")
                        ->orWhere('orders.billing_mobile_no', 'like', "%{$keywords}%")
                        ->orWhere('orders.billing_address_line1', 'like', "%{$keywords}%")
                        ->orWhere('orders.billing_state', 'like', "%{$keywords}%")
                        ->orWhere('orders.order_no', 'like', "%{$keywords}%")
                        ->orWhere('merchant_orders.order_status', 'like', "%{$keywords}%")
                        ->orWhereDate("merchant_orders.created_at", $date);
                }
                if ($fromDate && $toDate) {
                    $query->whereDate("merchant_orders.created_at", ">=", $fromDate)
                        ->whereDate("merchant_orders.created_at", "<=", $toDate);
                }
            })
            ->addIndexColumn()
            ->editColumn('billing_info', function ($row) {
                $billing_info = '';
                $billing_info .= '<div class="font-weight-bold">' . $row['billing_name'] . '</div>';
                $billing_info .= '<div class="">' . $row['billing_mobile_no'] . '</div>';

                return $billing_info;
            })
            ->editColumn('product_name', function ($row) {
                return ucwords($row->product_name);
            })
            ->editColumn('amount', function ($row) {

                $profit_margin_percentage = $row->profit_margin_percentage;
                return $row->product_amount - (($profit_margin_percentage / 100) * $row->product_amount); // - $profit_margin;
            })

            ->editColumn('payment_status', function ($row) {
                return ucwords($row->payment_status);
            })
            ->editColumn('merchant_status', function ($row) {
                return ucwords($row->merchant_status);
            })

            ->editColumn('created_at', function ($row) {
                $created_at = Carbon::createFromFormat('Y-m-d H:i:s', $row['created_at'])->format('d-m-Y');
                return $created_at;
            })

            ->addColumn('action', function ($row) {
                $edit_btn = '<a href="javascript:void(0)" onclick="return openOrderStatusModal(' . $row->merchant_order_id . ',' . auth()->user()->id . ')" class="btn btn-icon btn-active-primary btn-light-primary mx-1 w-30px h-30px" >
                            <i class="fa fa-edit"></i>
                        </a>';
                $view_btn = '<a href="javascript:void(0)" onclick="return viewOrder('.$row->id.','.$row->order_product_id.')" class="btn btn-icon btn-active-primary btn-light-primary mx-1 w-30px h-30px" >
                <i class="fa fa-eye"></i>
            </a>';
                return $edit_btn.$view_btn;
            })
            ->rawColumns(['action', 'status', 'billing_info', 'payment_status', 'merchant_status', 'created_at', 'product_name', 'amount']);
        return $datatables->make(true);
    }
        $breadCrum = array('Order');
        $title      = 'Order';
        $merchant_id = $id;
        return view('platform.merchant_order.index',compact('title','breadCrum', 'merchant_id'));
        }
    }
    public function export()
    {
        return Excel::download(new MerchantExport, 'merchant_list.xlsx');
    }

    public function orderexport()
    {
       
        return Excel::download(new MerchantOrderExport, 'merchant_order.xlsx');
    }

    public function merchantOrders(Request $request)
    {

        if ($request->ajax()) {
            $data = Order::selectRaw('DISTINCT mm_order_products.id as order_product_id,
                                        mm_payments.order_id,
                                        mm_merchant_orders.id as merchant_order_id,
                                        mm_merchant_orders.order_status as merchant_order_status,
                                        mm_orders.*,
                                        mm_order_products.quantity as order_quantity,
                                        mm_merchants.merchant_no,
                                        mm_merchants.id as merchant_id,
                                        mm_merchant_orders.seller_price as total_amount,
                                        mm_merchant_orders.merchant_profit_margin,
                                        mm_merchant_orders.total as product_value,
                                        mm_order_products.product_id,
                                        mm_payments.status as payment_status,
                                        mm_order_products.assigned_to_merchant,
                                        mm_order_products.assigned_seller_1,
                                        mm_order_products.assigned_seller_2,
                                        mm_order_products.status as order_status',)
                            ->leftJoin('merchant_orders', 'merchant_orders.order_id', '=', 'orders.id')
                            ->Join('order_products', 'order_products.order_id', '=', 'orders.id')
                            ->Join('merchants','merchants.id','=', 'merchant_orders.merchant_id')
                            ->join('payments', 'payments.order_id', '=', 'orders.id')
                            ->groupBy('merchant_orders.id','orders.id')
                            ->orderBy('orders.id', 'desc');
            $filter_subCategory   = '';
            $status = $request->get('status');
            $keywords = $request->get('search')['value'];
            $fromDate =  $request->get('fromDate');
            $toDate =  $request->get('toDate');

            $datatables =  DataTables::of($data)
                ->filter(function ($query) use ($keywords, $status, $fromDate, $toDate, $filter_subCategory) {
                    if ($status) {
                        return $query->where('order_products.assigned_to_merchant', $status);
                    }
                    if ($keywords) {
                        $date = date('Y-m-d', strtotime($keywords));
                        return $query->where('orders.billing_name','like',"%{$keywords}%")
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
                    $billing_info .= '<div class="font-weight-bold">'.$row['billing_name'].'</div>';
                    $billing_info .= '<div class="">'.$row['billing_mobile_no'].'</div>';
                    // $billing_info .= '<div class="">'.$row['billing_address_line1'].'</div>';

                    return $billing_info;
                })

                ->editColumn('payment_status', function ($row) {
                    return ucwords($row->payment_status);
                })
                ->editColumn('status', function ($row) {
                    $order_status = OrderStatus::where('id', $row->order_status)->select('status_name')->pluck('status_name')->first();
                    return ucwords($order_status);
                })
                ->editColumn('assigned_seller_1', function ($row) {
                    return Merchant::getMerchantNo($row->assigned_seller_1);
                })
                ->editColumn('assigned_seller_2', function ($row) {
                    return Merchant::getMerchantNo($row->assigned_seller_2);
                })
                ->editColumn('created_at', function ($row) {
                    $created_at = Carbon::createFromFormat('Y-m-d H:i:s', $row['created_at'])->format('d-m-Y h:i a');
                    return $created_at;
                })
                    ->editColumn('merchant_order_status', function ($row) {
                        $orderStatus = MerchantOrderStatus::where('order_status', $row->merchant_order_status)->select('order_status_name')->pluck('order_status_name')->first();
                        
                        switch ($orderStatus) {
                            case 'Accepted':
                                return '<span class="badge badge-light text-success">Accepted</span>';
                            case 'Pending':
                                return '<span class="badge badge-light text-warning">Pending</span>';
                            case 'Shipped':
                                return '<span class="badge badge-light text-dark">Shipped</span>';
                            case 'Rejected':
                                return '<span class="badge badge-light text-danger">Rejected</span>';
                            case 'Delivered':
                                return '<span class="badge badge-light text-info">Delivered</span>';
                            case 'Exchanged':
                                return '<span style="color:#87CEEB" class="badge badge-light">Exchanged</span>';
                            case 'Cancelled':
                                return '<span class="badge badge-light text-danger">Cancelled</span>';
                            case 'Cancel Requested':
                                return '<span class="badge badge-light text-muted">Cancel Requested</span>';
                            default:
                                return ucwords($orderStatus);
                        }
                    }) 
                ->editColumn('profit_margin', function ($row) {
                    // dd($row->total_amount);
                    $profit_margin = Merchant::getProfitMargin($row->product_id, $row->merchant_id, $row->total_amount);
                    //dd(number_format($row->total_amount - $profit_margin, 2));
                    return number_format($row->total_amount - $profit_margin, 2);
                })
                ->editColumn('merchant_profit_margin', function ($row) {
                    $profit_margin = Merchant::getProfitMarginPercentage($row->product_id, $row->merchant_id, $row->total_amount);
                    return $profit_margin;
                })
                ->addColumn('action', function ($row) {
                    $view_btn = '<a href="javascript:void(0)" onclick="return viewOrder('.$row->id.','.$row->order_product_id.')" class="btn btn-icon btn-active-primary btn-light-primary mx-1 w-30px h-30px" >
                    <i class="fa fa-eye"></i>
                </a>';

                    // $view_btn .= '<a href="javascript:void(0)" onclick="return openOrderStatusModal('.$row->merchant_order_id.')" class="btn btn-icon btn-active-primary btn-light-primary mx-1 w-30px h-30px" >
                    //             <i class="fa fa-edit"></i>
                    //         </a>';

                    // $view_btn .= '<a target="_blank" href="'.asset('storage/invoice_order/'.$row->order_no.'.pdf').'" tooltip="Download Invoice"  class="btn btn-icon btn-active-success btn-light-success mx-1 w-30px h-30px" >
                    //                 <i class="fa fa-download"></i>
                    //             </a>';

                    return $view_btn;
                })
                ->rawColumns(['action','merchant_order_status', 'assigned_to_merchant', 'status', 'payment_status', 'order_status', 'created_at', 'amount']);
            return $datatables->make(true);
        }
        $breadCrum = array('Merchant Orders');
        $title      = 'Merchant Orders';
        return view('platform.merchant_order.merchant_order_list',compact('title','breadCrum'));

    }

    public function openOrderStatusModal(Request $request)
    {

        $order_id = $request->id;
        $order_status_id = $request->order_status_id;
        $modal_title = 'Update Order Status';

        $info = MerchantOrder::find($order_id);
        dd($info);
        $order_status_info = MerchantOrderStatus::select('id', 'order_status', 'order_status_name')->get();


        return view('platform.merchant_invoice.order_status_modal', compact('info', 'order_status_info') );

    }

    public function changeOrderStatus(Request $request)
    {
        $id             = $request->id;
        $required = ($request->order_status) == 'reject' ? 'required|' : null;
        $validator      = Validator::make($request->all(), [
            'order_status' => 'required|string',
            'order_reject_reason_id' => $required . 'numeric|nullable',

        ]);
        if ($validator->passes()) {

            $info = MerchantOrder::find($id);
            $info->order_status = $request->order_status;
            $info->order_reject_reason_id = $request->order_reject_reason_id;
            $info->shipment_tracking_code = $request->shipment_tracking_code;

            $info->update();
            $merchant = Merchant::find($info->merchant_id);
            $order_id = $info->order_id;
            $order_info = Order::find($order_id);
            $to_email_address_admin = env('MAIL_FROM_FOR_ADMIN');
            if ($request->order_status == 'ship') {
                $order_status_id = 4;
                $order_info->order_status_id = 4;
                $action = 'Order Shipped';
                $email_slug_admin = 'order-shipped-notify-admin';
                $merchant_name = $merchant->first_name;
                $order_no = $order_info->order_no;
                $this->sendEmailNotification($email_slug_admin, $merchant_name, $order_no, $to_email_address_admin);

                $email_slug_customer = 'order-shipped';
                $to_email_address_customer =$order_info->billing_email;
                $customer_name = $order_info->billing_name;
                $globalInfo = GlobalSettings::first();
                $extract = array(
                    'name' => $order_info->billing_name,
                    'regards' => $globalInfo->site_name,
                    'company_website' => '',
                    'company_mobile_no' => $globalInfo->site_mobile_no,
                    'company_address' => $globalInfo->address,
                    'customer_login_url' => env('WEBSITE_LOGIN_URL'),
                    'order_no' => $order_info->order_no
                );
                $this->sendEmailNotificationByArray($email_slug_customer, $extract, $to_email_address_customer);

                $order_info->status = 'shipped';
            } else if ($request->order_status == 'accept') {
                $order_status_id = 8;
                $order_info->order_status_id = 8;
                $action = 'Order Accepted';
                $email_slug_admin = 'order-accepted';
                $merchant_name = $merchant->first_name;
                $order_no = $order_info->order_no;
                $this->sendEmailNotification($email_slug_admin, $merchant_name, $order_no, $to_email_address_admin);
                $order_info->status = 'accepted';
            } else if ($request->order_status == 'reject') {
                $auto_assign_next_merchant = $this->autoAssignOrder($order_info);
                $order_status_id = 7;
                $order_info->order_status_id = 7;
                $email_slug_admin = 'order-rejected';
                $merchant_name = $merchant->first_name;
                $order_no = $order_info->order_no;
                $this->sendEmailNotification($email_slug_admin, $merchant_name, $order_no, $to_email_address_admin);
                $order_info->status = 'rejected';
            }else if ($request->order_status == 'cancelled') {
                $order_status_id = 3;
                $order_info->order_status_id = 3;
                // $email_slug_admin = 'order-cancelled';
                // $merchant_name = $merchant->first_name;
                // $order_no = $order_info->order_no;
                // $this->sendEmailNotification($email_slug_admin, $merchant_name, $order_no, $to_email_address_admin);
                $order_info->status = 'cancelled';
            }

            $order_info->update();

            $message    = (isset($id) && !empty($id)) ? 'Updated Successfully' : 'Added successfully';
            $error = 0;
        } else {
            $error      = 1;
            $message    = $validator->errors()->all();
        }
        return response()->json(['error' => $error, 'message' => $message]);
    }

    public function orderView(Request $request)
    {
        $order_id = $request->id;
        $order_product_id = $request->order_product_id;
        $order_info = Order::selectRaw('mm_order_products.sub_total as product_amount, mm_payments.order_id,mm_payments.payment_no,mm_payments.status as payment_status,mm_orders.*, mm_products.product_name as product_name,mm_order_products.quantity as order_quantity, mm_merchant_orders.id as merchant_order_id, mm_merchant_orders.order_status as merchant_status, mm_merchant_orders.merchant_profit_margin as profit_margin_percentage, mm_merchant_orders.seller_price, mm_merchant_orders.total')
            ->join('merchant_orders', 'merchant_orders.order_id', '=', 'orders.id')
            ->join('order_products', 'order_products.id', '=', 'merchant_orders.order_product_id')
            ->join('products', 'order_products.product_id', '=', 'products.id')
            ->join('payments', 'payments.order_id', '=', 'orders.id')
            ->where('order_products.id', $order_product_id)
            ->where('merchant_orders.order_id', $order_id)
            ->first();
        $orderItems = OrderProduct::find($order_product_id);
        $modal_title = 'View Order';
        $globalInfo = GlobalSettings::first();
        $view_order = view('platform.merchant_invoice.view_invoice', compact('order_info', 'globalInfo', 'orderItems'));
        return view('platform.merchant_invoice.view_modal', compact('view_order', 'modal_title'));

    }

}
