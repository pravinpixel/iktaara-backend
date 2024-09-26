<?php

namespace App\Listeners;

use App\Events\CancelOrder;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use App\Mail\DynamicMail;
use App\Models\GlobalSettings;
use App\Models\Master\EmailTemplate;
use App\Models\Order;
use App\Models\Seller\Merchant;
use Mail;

class CancelOrderNotifications
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
     * @param  \App\Events\CancelOrder  $event
     * @return void
     */
    public function handle(CancelOrder $event): void
    {

        $order_id = $event->data['order_id'];
        $product_id = $event->data['product_id'];

        $orderInfo = Order::select('orders.customer_id', 'orders.order_no', 'orders.billing_email as customer_mail', 'orders.created_at', 'op.assigned_seller_1 as seller_1', 'op.assigned_seller_2 as seller_2')
            ->join('order_products AS op', 'orders.id', '=', 'op.order_id')
            ->where('orders.id', $order_id)
            ->where('op.product_id', $product_id)
            ->first();

        $sellerId = ($orderInfo['seller_2'] == null) ? $orderInfo['seller_1'] : $orderInfo['seller_2'];


        $emailTemplate = EmailTemplate::select('email_templates.*')
            ->join('sub_categories', 'sub_categories.id', '=', 'email_templates.type_id')
            ->where('sub_categories.slug', 'order-cancel-requested')->first();

        $globalInfo = GlobalSettings::select('site_name', 'site_email', 'site_mobile_no', 'address')->first();

        $dynamic_content = 'Order no : ' . $orderInfo->order_no . ', Order Date:' . date('d M Y H:i A', strtotime($orderInfo->created_at));

        $extract = array(
            'name' => '',
            'dynamic_content' => $dynamic_content,
            'regards' => $globalInfo->site_name,
            'company_website' => '',
            'company_mobile_no' => $globalInfo->site_mobile_no,
            'company_address' => $globalInfo->address,
            'customer_login_url' => env('WEBSITE_LOGIN_URL'),
            'cancel_reason' => $event->data['cancel_reason']
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

        $this->sendMail([
            'template' => $templateMessage,
            'title' => $title,
            'to' => $orderInfo->customer_mail
        ]);

        $this->sendMail([
            'template' => $templateMessage,
            'title' => $title,
            'to' => $globalInfo->site_email
        ]);

        if ($sellerId !== 0) {
            $merchant = Merchant::find($sellerId);
            if ($merchant != null) {
                $this->sendMail([
                    'template' => $templateMessage,
                    'title' => $title,
                    'to' => $merchant->email
                ]);
            }
        }
    }

    protected function sendMail($data)
    {
        $send_mail = new DynamicMail($data['template'], $data['title']);
        //return $send_mail->render();
        Mail::to($data['to'])->send($send_mail);
    }
}
