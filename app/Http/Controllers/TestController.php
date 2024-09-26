<?php

namespace App\Http\Controllers;

use App\Http\Resources\MenuAllResource;
use App\Mail\TestMail;
use App\Models\GlobalSettings;
use App\Models\Master\Brands;
use App\Models\Master\EmailTemplate;
use App\Models\Order;
use App\Models\Product\Product;
use App\Models\Product\ProductCategory;
use App\Models\SmsTemplate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use PDF;
use Mail;

class TestController extends Controller
{
    public function index(Request $request)
    {

        $number = ['919551706025'];
        $name   = 'Durairaj';
        $orderId = 'IOP9090909P';
        $companyName = 'Iktaraa';
        $credentials = 'durairamyb@mail.com/09876543456';
        $message = "Dear $name, Ref.Id $orderId, Thank you for register with $companyName. Your credentials are $credentials. -IKTARAA";
        sendSMS($number, $message, []);

        // $response = Http::post('https://apiv2.shiprocket.in/v1/external/auth/login',[
        //     'header' => 'Content-Type: application/json',
        //     'email' => 'duraibytes@gmail.com',
        //     'password' => 'Pixel@2022'
        // ]);

        // dd( $response );

    }

    public function sendSms($sms_type, $details = [])
    {
        $info = SmsTemplate::where('sms_type', $sms_type)->first();
        if (isset($info) && !empty($info)) {

            $number = ['919551706025'];
            $details = array(
                'name' => 'durairja',
                'reference_id' => '88978979',
                'company_name' => env('APP_NAME'),
                'login_details' => 'loginId:durairamyb@mail.com,password:09876543456',
                'mobile_no' => ['919551706025']
            );
            // $name   = 'Durairaj';
            // $reference_id = 'ORD2015';
            // $company_name = $info->company_name;
            // $credential = 'email/password';
            // $subscribtion_id = '#SUB2022';
            // $rupees = 'RS250000';
            // $payment_method = 'online razorpay';
            // $first_name  = 'Durai';
            // $last_name  = 'raj';
            // $order_no = 'ORD2013';
            // $company_url  = 'https://www.onlinemuseemusical.com/';
            // $latest_update = 'Latest Updates';
            // $tracking_no = '#um89898990000009';
            // $tracking_url = 'https://www.onlinemuseemusical.com/';

            $templateMessage = $info->template_content;
            $templateMessage = str_replace("{", "", addslashes($templateMessage));
            $templateMessage = str_replace("}", "", $templateMessage);

            extract($details);

            eval("\$templateMessage = \"$templateMessage\";");

            $params = array(
                'entityid' => $info->peid_no,
                'tempid' => $info->tdlt_no,
                'sid'   => urlencode(current(explode(",", $info->header)))
            );

            sendSMS($number, $templateMessage, $params);
        }
    }

    public function invoiceSample(Request $request)
    {
        $info = 'teste';

        $order_info = Order::find(5);
        $globalInfo = GlobalSettings::first();
        $pdf = PDF::loadView('platform.invoice.index', compact('order_info', 'globalInfo'));
        Storage::put('public/invoice_order/' . $order_info->order_no . '.pdf', $pdf->output());
        // $pdf = PDF::loadView('platform.invoice.index', compact('order_info', 'globalInfo'))->setPaper('a4', 'portrait');
        // return $pdf->stream('test.pdf');
    }

    public function sendMail()
    {
        // $email = 'duraibytes@gmail.com';

        // $mailData = [
        //     'title' => 'Demo Email',
        //     'url' => 'https://www.positronx.io'
        // ];

        // Mail::to($email)->send(new TestMail($mailData));

        // return response()->json([
        //     'message' => 'Email has been sent.'
        // ]);

        $emailTemplate = EmailTemplate::select('email_templates.*')
            ->join('sub_categories', 'sub_categories.id', '=', 'email_templates.type_id')
            ->where('sub_categories.slug', 'new-registration')->first();

        $globalInfo = GlobalSettings::first();


        $extract = array(
            'name' => 'Durairaj',
            'regards' => $globalInfo->site_name,
            'company_website' => '',
            'company_mobile_no' => $globalInfo->site_mobile_no,
            'company_address' => $globalInfo->address
        );
        $templateMessage = $emailTemplate->message;
        $templateMessage = str_replace("{", "", addslashes($templateMessage));
        $templateMessage = str_replace("}", "", $templateMessage);
        extract($extract);
        eval("\$templateMessage = \"$templateMessage\";");

        $body = [
            'content' => $templateMessage,
            'title' => $emailTemplate->title
        ];
        $send_mail = new TestMail($templateMessage, $emailTemplate->title);
        // return $send_mail->render();
        // Mail::to("durairaj.pixel@gmail.com")->send($send_mail);
        sendEmailWithBcc(env('MAIL_FROM_FOR_ADMIN'), $send_email);
    }

    public function generateSiteMap(Request $request)
    {
        $products = Product::where('status', 'published')->get();
        $pages = array(
            'https://www.iktaraa.com/buy',
            'https://www.iktaraa.com/buy/privacy-policy',
            'https://www.iktaraa.com/buy/terms-conditions',
            'https://www.iktaraa.com/buy/return-cancel',
            'https://www.iktaraa.com/buy/category/shop-by-brand',

        );
        $brands = Brands::all();




        $data           = ProductCategory::where(['status' => 'published'])
            ->where('parent_id', 0)
            ->orderBy('order_by', 'asc')
            ->get();
        $tmp = [];
        foreach ($data as $category_data) {

            $childTmp = $childTmpinnerChild = [];
            $tmp1['id']        = $category_data->id;
            $tmp1['name']      = $category_data->name;
            $tmp1['slug']      = $category_data->slug;
            $tmp1['created_at']      = $category_data->created_at;
            foreach ($category_data->childCategory as $child) {
                $childLevelCategory = ProductCategory::where('status', 'published')->where('parent_id', $child->id)->get();

                $childTmpinnerChild = [];
                if (isset($childLevelCategory) && !empty($childLevelCategory)) {
                    foreach ($childLevelCategory as $childLevel) {
                        $childTmp1['id']    = $childLevel->id;
                        $childTmp1['name'] = $childLevel->name;
                        $childTmp1['slug']   = $childLevel->slug;
                        $childTmp1['created_at']   = $childLevel->created_at;
                        $childTmpinnerChild[]         = $childTmp1;
                    }
                    $innerTmp['id']     = $child->id;
                    $innerTmp['name']   = $child->name;
                    $innerTmp['slug']   = $child->slug;
                    $innerTmp['created_at']   = $child->created_at;
                    $innerTmp['innerchild'] = $childTmpinnerChild;
                    $childTmp[]         = $innerTmp;
                }
            }
            $tmp1['child']       = $childTmp;
            $tmp[] = $tmp1;

            // $childTmp['inner_child'][] = $childTmpinnerChild;
        }
        // var_dump($categories);
        return response()->view('site-map', compact('products', 'brands', 'pages', 'tmp'))->header('Content-Type', 'text/xml');
    }
}
