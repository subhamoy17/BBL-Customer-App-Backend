<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;



class BootcampSessionNotification extends Notification implements ShouldQueue
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

        if($this->notifydata['status']=='Boocked BootcampSession by Customer')
        {
         return (new MailMessage)->view(
                  'bootcampsessionrequestemail',
                  [
                'enquiredTime' => Carbon::now(),
                'customer_name'=>$this->notifydata['customer_name'],
                'customer_email'=>$this->notifydata['customer_email'],
                'customer_phone'=>$this->notifydata['customer_phone'],
                'status'=>$this->notifydata['status'],
                'url'=>$this->notifydata['url'],
                'all_data'=>$this->notifydata['all_data'],
                
                 ]);
        }
        else
        {
            return (new MailMessage)->view(
              'bootcampsessionrequestemail',
              [
            'enquiredTime' => Carbon::now(),
            'customer_name'=>$this->notifydata['customer_name'],
            'customer_email'=>$this->notifydata['customer_email'],
            'customer_phone'=>$this->notifydata['customer_phone'],
            'status'=>$this->notifydata['status'],
            'url'=>$this->notifydata['url'],
            'session_booked_on'=>$this->notifydata['session_booked_on'],
            'session_booking_date'=>$this->notifydata['session_booking_date'],
            'session_booking_time'=>$this->notifydata['session_booking_time'],
            'session_booking_day'=>$this->notifydata['session_booking_day'],
            'cancelled_reason'=>$this->notifydata['cancelled_reason'],                   
            'schedule_address'=>$this->notifydata['schedule_address']                   
          ]);
        }
    }
}