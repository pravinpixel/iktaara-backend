<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class CustomerRequestMail extends Mailable
{
    use Queueable, SerializesModels;

    public $data;
    public $title;

    public function __construct($data, $title)
    {
        $this->data = $data;    
        $this->title = $title." from ".ucfirst($data->customer_categories);
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->markdown('email.common.customerRequest')->subject($this->title)->with(['data' => $this->data]);
    }
}
