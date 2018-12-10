<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;



class SessionRequestNotification extends Notification implements ShouldQueue
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

             if($this->notifydata['status']=='Sent Session Request by trainer')
        {
            return (new MailMessage)->view(
              'emails.sessionrequestemail',
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
            'trainer_name'=>$this->notifydata['trainer_name'],
            'decline_reason'=>$this->notifydata['decline_reason'],            
            'sending_trainer'=>$this->notifydata['sending_trainer'],            
          ]);
        }

        else
        {
             return (new MailMessage)->view(
              'emails.sessionrequestemail',
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
            'trainer_name'=>$this->notifydata['trainer_name'],
            'decline_reason'=>$this->notifydata['decline_reason'],            
          ]);
        }

    }
}