<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use NotificationChannels\WebPush\WebPushChannel;
use NotificationChannels\WebPush\WebPushMessage;

class IdVerified extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', WebPushChannel::class];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Successfully verified your ID')
            ->line('Hello, ' . $notifiable->name . '!')
            ->line('We\'re pleased to inform you that your ID has been successfully verified. This confirms your identity in our system, granting you full access to our services. Should you have any further inquiries, don\'t hesitate to reach out. Thank you for your cooperation.')
            ->line('Best regards,')
            ->line('Abysinia Stars Team');
    }

    public function toWebPush(object $notifiable, $notification)
    {
        return (new WebPushMessage)
            ->title('Successfully verified your ID')
            // ->icon('/notification-icon.png')
            ->body('Hello, ' . $notifiable->name . '! We\'re pleased to inform you that your ID has been successfully verified. Thank you for your cooperation.')
            ->action('View account', 'view_account')
            ->data(['id' => $notification->id]);
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
