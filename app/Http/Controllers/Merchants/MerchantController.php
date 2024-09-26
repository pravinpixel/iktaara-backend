<?php

namespace App\Http\Controllers\Merchants;

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
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use DataTables;
use Carbon\Carbon;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

class MerchantController extends Controller
{

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = RouteServiceProvider::HOME;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        // $this->middleware('guest')->except('logout');
        // $this->middleware('guest:merchant')->except('logout');

        $this->middleware('guest');
        $this->middleware('guest:merchant');
    }

    public function register(){
        $country                = Country::where('status',1)->get();
        $state                  = State::where('status',1)->get();
        $city                   = City::where('status',1)->get();
        $pinCode                = Pincode::where('status',1)->get();
        return view("seller_auth.register", compact('city', 'state', 'country', 'pinCode'));

    }

    public function postRegister(Request $request){
        // dd($request);
        $request->validate([
            'first_name' => 'required',
            'email' => 'required|email|unique:merchants',
            'password' => 'required|min:6',
            'mobile_no' => 'required|digits:10',
            'post_code' => 'required'
        ]);
        $data = $request->all();
        $data['password'] = Hash::make($request->password);
        $data['merchant_no'] = getMerchantNo();
        $createMerchant = Merchant::create($data);
        if($createMerchant){
             /** send email for new customer */
             $emailTemplate = EmailTemplate::select('email_templates.*')
             ->join('sub_categories', 'sub_categories.id', '=', 'email_templates.type_id')
             ->where('sub_categories.slug', 'seller-registration')->first();

         $globalInfo = GlobalSettings::first();

         // $link = 'http://192.168.0.35:3000/#/verify-account/' . $token_id;


         $extract = array(
             'name' => $request->firstName. ' ' .$request->lastName,
             'regards' => $globalInfo->site_name,
             'company_website' => $globalInfo->site_name,
             'company_mobile_no' => $globalInfo->site_mobile_no,
             'company_address' => $globalInfo->address,
             'user_name' => $request->email,
             'password' => $request->password
         );

         $templateMessage = $emailTemplate->message;
         $templateMessage = str_replace("{", "", addslashes($templateMessage));
         $templateMessage = str_replace("}", "", $templateMessage);
         extract($extract);
         eval("\$templateMessage = \"$templateMessage\";");

         $send_mail = new DynamicMail($templateMessage, $emailTemplate->title);
         // return $send_mail->render();
         sendEmailWithBcc($request->email, $send_mail);
        }

        return redirect("seller/dashboard")->withSuccess('Hi '.$request->first_name. 'You have Successfully loggedin to your Seller portal');
    }
}
