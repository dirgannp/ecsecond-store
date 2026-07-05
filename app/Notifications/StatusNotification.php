<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\BroadcastMessage;
class StatusNotification extends Notification
{
    use Queueable;
    private $details;
    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($details)
    {
        $this->details=$details;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        $channels = ['database'];

        if ($this->shouldBroadcast()) {
            $channels[] = 'broadcast';
        }

        return $channels;
    }

    private function shouldBroadcast()
    {
        $driver = config('broadcasting.default');

        if (in_array($driver, [null, 'null'], true)) {
            return false;
        }

        if ($driver === 'pusher') {
            return filled(config('broadcasting.connections.pusher.key'))
                && filled(config('broadcasting.connections.pusher.secret'))
                && filled(config('broadcasting.connections.pusher.app_id'));
        }

        return true;
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    // public function toMail($notifiable)
    // {
    //     return (new MailMessage)
    //                 ->subject('Status Notification')
    //                 ->from(env('MAIL_USERNAME','test@gmail.com'),'E-shop')
    //                 ->line($this->details['title'])
    //                 ->action('View Order', $this->details['actionURL'])
    //                 ->line('Thank you!');
    // }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    // public function toArray($notifiable)
    // {
    //     return [
    //         'title'=>$this->details['title'],
    //         'actionURL'=>$this->details['actionURL'],
    //         'fas'=>$this->details['fas']
    //     ];
    // }
    public function toArray($notifiable)
    {
        return [
            'title'=>$this->details['title'],
            'actionURL'=>$this->details['actionURL'],
            'fas'=>$this->details['fas']
        ];
    }


    
    /**
     * Get the broadcastable representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return BroadcastMessage
     */
    public function toBroadcast($notifiable)
    {
        return new BroadcastMessage([
            'title' => $this->details['title'],
            'actionURL' => $this->details['actionURL'],
            'url' => route('admin.notification', $this->id),
            'fas' => $this->details['fas'],
            'time' => date('F d, Y h:i A')
        ]);
    }


    
}
