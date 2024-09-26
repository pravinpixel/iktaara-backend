<?php

namespace App\Http\Controllers;

use App\Mail\CustomerRequestMail;
use App\Models\CustomerRequestDetail;
use Illuminate\Http\Request;
use Validator;
use Mail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use DataTables;
class CustomerRequestController extends Controller
{
    public function createForm(Request $request){

        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'email' => 'required|email',
            'mobile_no' => 'numeric|digits:10',
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
                    $result = CustomerRequestDetail::create($data);
                    if(!empty($result)){

                        $send_mail = new CustomerRequestMail($result, "Customer request");
                        // return $send_mail->render();
                        Mail::to(env('MAIL_FROM_FOR_ADMIN'))->send($send_mail);
                        return response()->json(['error' => 0, 'message' => "Enquiry form submitted successfully"], 200);
                    }else{
                        return response()->json(['error' => 1, 'message' => "Enquiry submission failed"], 500);
                    }

                });
            }catch (\Exception $e) {
                $error = 1;
                return response()->json(['error' => 1, 'message' => $e->getMessage()], 500);
            }
        }else{
            $error      = 1;
            $message    = $validator->errors()->toArray();
            return response()->json(['error' => $error, 'message' => $message], 400);
        }
    }

    public function index(Request $request)
    {
        $type = $request->query('type');

        if ($request->ajax()) {
            $ajax_type = $request->input('type');

            // $data = CustomerRequestDetail::select('customer_request_details.*');
            $data = CustomerRequestDetail::where( 'customer_categories', $ajax_type )->get();

            //$status = $request->get('status');
            $keywords = $request->get('search')['value'];
            $datatables =  DataTables::of($data);
                // ->filter(function ($query) use ($keywords) {
                //     if ($keywords) {
                //         $date = date('Y-m-d', strtotime($keywords));
                //         return $query->where('name','like',"%{$keywords}%")
                //                 ->orWhere('email', 'like', "%{$keywords}%")
                //                 ->orWhere('mobile_no', 'like', "%{$keywords}%")
                //                 ->orWhere('location', 'like', "%{$keywords}%")
                //                 ->orWhere('pincode', 'like', "%{$keywords}%")
                //                 ->orWhere('customer_categories', 'like', "%{$keywords}%")
                //                 ->orWhere('customer_designation', 'like', "%{$keywords}%")
                //                 ->orWhere('desc', 'like', "%{$keywords}%")
                //                 ->orWhereDate("customer_request_details.created_at", $date);
                //     }
                // });
            return $datatables->make(true);
        }
        $breadCrum  = array($type);
        $title      = ucfirst($type);
        return view('platform.customer-requests.'.$type, compact('breadCrum', 'title'));
    }
}
