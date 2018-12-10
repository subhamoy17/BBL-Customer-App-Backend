<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;


class PackagePurchaseNotification extends Notification implements ShouldQueue
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
        Log::debug(" Mail ".print_r($this,true));


            return (new MailMessage)->view(
              'emails.packagepurchaseemail',
              [
            'enquiredTime' => Carbon::now(),
            'package_name'=>$this->notifydata['package_name'],
            'slots_number'=>$this->notifydata['slots_number'],
            'package_validity'=>$this->notifydata['package_validity'],
            'package_purchase_date'=>$this->notifydata['package_purchase_date'],
            'package_amount'=>$this->notifydata['package_amount'],
            'payment_id'=>$this->notifydata['payment_id'],
            'payment_mode'=>$this->notifydata['payment_mode'],
            'customer_name'=>$this->notifydata['customer_name'],
            'customer_email'=>$this->notifydata['customer_email'],
            'customer_phone'=>$this->notifydata['customer_phone'],
            'status'=>$this->notifydata['status'],
            'url'=>$this->notifydata['url'],


            
          ]);

    }
}