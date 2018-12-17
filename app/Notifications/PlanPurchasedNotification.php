<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;


class PlanPurchasedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     *
     * @return void
     */

    protected $notifydata;
    public function __construct($notifydata)
    {
        $this->notifydata=$notifydata;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        //Log::debug(" Mail ".print_r($this,true));


            return (new MailMessage)->view(
              'planpurchasedemail',
              [
            'enquiredTime' => Carbon::now(),
            'product_name'=>$this->notifydata['product_name'],
            'no_of_sessions'=>$this->notifydata['no_of_sessions'],
            'product_validity'=>$this->notifydata['product_validity'],
            'product_purchase_date'=>$this->notifydata['product_purchase_date'],
            'product_amount'=>$this->notifydata['product_amount'],
            'order_id'=>$this->notifydata['order_id'],
            'payment_mode'=>$this->notifydata['payment_mode'],
            'customer_name'=>$this->notifydata['customer_name'],
            'customer_email'=>$this->notifydata['customer_email'],
            'customer_phone'=>$this->notifydata['customer_phone'],
            'status'=>$this->notifydata['status'],
            'url'=>$this->notifydata['url'],            
          ]);

    }
}