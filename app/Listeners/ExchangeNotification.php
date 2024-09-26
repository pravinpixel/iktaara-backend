<?php

namespace App\Listeners;

use App\Events\ProductExchange;
use App\Models\Order;
use App\Models\Seller\Merchant;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use App\Models\GlobalSettings;
use App\Models\Master\EmailTemplate;
use Mail;
use App\Mail\DynamicMail;

class ExchangeNotification
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  \App\Events\ProductExchange  $event
     * @return void
     */
    public function handle(ProductExchange $event): void
    {
        $item_id = $event->data['item_id'];
        $order_id = $event->data['order_id'];
        $product_id = $event->data['product_id'];
        $customer_id = $event->data['customer_id'];
        $seller_id = $event->data['seller_id'];
        $customer_email = $event->data['customer_email'] ?? '';
        $reason = $event->data['reason'];
        $status = $event->data['status'];

        $order =  Order::find($order_id);
        $globalInfo = GlobalSettings::select('site_name', 'site_email', 'site_mobile_no', 'address')->first();

        $templateData = [];

        if ($status == 1) {
            $templateData =  $this->prepareAcceptTemplate($event, $order, $globalInfo);
        } elseif ($status == 2) {
            $templateData =  $this->prepareRejectTemplate($event, $order, $globalInfo);
        } else {
            $templateData =  $this->prepareRequestTemplate($event, $order, $globalInfo);
        }

        $this->sendMail([
            'template' => $templateData['templateMessage'],
            'title' => $templateData['title'],
            'to' => $order->billing_email,
        ]);

        $this->sendMail([
            'template' => $templateData['templateMessage'],
            'title' => $templateData['title'],
            'to' => $globalInfo->site_email
        ]);

        if ($seller_id != NULL)  {
            $merchant = Merchant::find($seller_id);
            if ($merchant != null) {
                $this->sendMail([
                    'template' => $templateData['templateMessage'],
                    'title' => $templateData['title'],
                    'to' => $merchant->email
                ]);
            }
        }
    }

    protected function prepareRequestTemplate($event, $order, $globalInfo)
    {
        $emailTemplate = EmailTemplate::select('email_templates.*')
            ->join('sub_categories', 'sub_categories.id', '=', 'email_templates.type_id')
            ->where('sub_categories.slug', 'order-exchange-requested')->first();

        $dynamic_content = 'Order no : ' . $order->order_no . ', Order Date:' . date('d M Y H:i A', strtotime($order->created_at));

        $extract = array(
            'name' => '',
            'dynamic_content' => $dynamic_content,
            'regards' => $globalInfo->site_name,
            'company_website' => '',
            'company_mobile_no' => $globalInfo->site_mobile_no,
            'company_address' => $globalInfo->address,
            'customer_login_url' => env('WEBSITE_LOGIN_URL'),
            'reason' => $event->data['reason']
        );
        $templateMessage = $emailTemplate->message;
        $templateMessage = str_replace("{", "", addslashes($templateMessage));
        $templateMessage = str_replace("}", "", $templateMessage);
        extract($extract);
        eval("\$templateMessage = \"$templateMessage\";");

        $title = $emailTemplate->title;
        $title = str_replace("{", "", addslashes($title));
        $title = str_replace("}", "", $title);
        eval("\$title = \"$title\";");

        return ['templateMessage' => $templateMessage, 'title' => $title];
    }

    protected function prepareRejectTemplate($event, $order, $globalInfo)
    {
        $emailTemplate = EmailTemplate::select('email_templates.*')
        ->join('sub_categories', 'sub_categories.id', '=', 'email_templates.type_id')
        ->where('sub_categories.slug', 'order-exchange-reject')->first();

        $dynamic_content = 'Order no : ' . $order->order_no . ', Order Date:' . date('d M Y H:i A', strtotime($order->created_at));

        $extract = array(
            'name' => '',
            'dynamic_content' => $dynamic_content,
            'regards' => $globalInfo->site_name,
            'company_website' => '',
            'company_mobile_no' => $globalInfo->site_mobile_no,
            'company_address' => $globalInfo->address,
            'customer_login_url' => env('WEBSITE_LOGIN_URL'),
            'reason' => $event->data['reason']
        );
        $templateMessage = $emailTemplate->message;
        $templateMessage = str_replace("{", "", addslashes($templateMessage));
        $templateMessage = str_replace("}", "", $templateMessage);
        extract($extract);
        eval("\$templateMessage = \"$templateMessage\";");

        $title = $emailTemplate->title;
        $title = str_replace("{", "", addslashes($title));
        $title = str_replace("}", "", $title);
        eval("\$title = \"$title\";");

        return ['templateMessage' => $templateMessage, 'title' => $title];
    }

    protected function prepareAcceptTemplate($event, $order, $globalInfo)
    {
        $emailTemplate = EmailTemplate::select('email_templates.*')
        ->join('sub_categories', 'sub_categories.id', '=', 'email_templates.type_id')
        ->where('sub_categories.slug', 'order-exchange-accepted')->first();

        $dynamic_content = 'Order no : ' . $order->order_no . ', Order Date:' . date('d M Y H:i A', strtotime($order->created_at));

        $extract = array(
            'name' => '',
            'dynamic_content' => $dynamic_content,
            'regards' => $globalInfo->site_name,
            'company_website' => '',
            'company_mobile_no' => $globalInfo->site_mobile_no,
            'company_address' => $globalInfo->address,
            'customer_login_url' => env('WEBSITE_LOGIN_URL'),
            'reason' => $event->data['reason']
        );
        $templateMessage = $emailTemplate->message;
        $templateMessage = str_replace("{", "", addslashes($templateMessage));
        $templateMessage = str_replace("}", "", $templateMessage);
        extract($extract);
        eval("\$templateMessage = \"$templateMessage\";");

        $title = $emailTemplate->title;
        $title = str_replace("{", "", addslashes($title));
        $title = str_replace("}", "", $title);
        eval("\$title = \"$title\";");

        return ['templateMessage' => $templateMessage, 'title' => $title];
    }

    protected function sendMail($data)
    {
        $send_mail = new DynamicMail($data['template'], $data['title']);
        //return $send_mail->render();
        Mail::to($data['to'])->send($send_mail);
    }
}
