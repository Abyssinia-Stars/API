<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Config;
use Ichtrojan\Otp\Otp;

class CustomVerifyEmail extends Notification
{
    use Queueable;

    public $token;
    


    /**
     * Create a new notification instance.
     */
    public function __construct()
    {
        // $out = new \Symfony\Component\Console\Output\ConsoleOutput();
        // $out->writeln("HERE CONSTRUZCKGOJIHD");

        // $this->token = $token;
        // $this->id = $id;
        //
    }

    function generateOtp($identifier, $type='numeric', $length = 4, $validity=15){
        return (new Otp)->generate($identifier, $type, $length, $validity);;
       
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    protected function verificationUrl($notifiable)
    {
        return URL::temporarySignedRoute(
            'verification.verify', 
            Carbon::now()->addMinutes(Config::get('auth.verification.expire', 60)), 
            ['id' => $notifiable->getKey(), 'hash' => sha1($notifiable->getEmailForVerification())]
        );
    }

    public function toMail(object $notifiable): MailMessage
    {

     
        $verificationUrl = $this->verificationUrl($notifiable);
        // generateOtp($notifiable->getKey());
        $out = new \Symfony\Component\Console\Output\ConsoleOutput();

        // $out->writeln($notifiable->getEmailForVerification());
       

        $otp = (new Otp)->generate($notifiable->getEmailForVerification(), 'numeric', 4, 60);
    

        return (new MailMessage)
                    ->line('There has been a request to verify OTP')
                    ->action('Notification Action',$verificationUrl)
                    ->line("Your OTP is " . $otp->token )
                    ->line('Thank you for using our application!');
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
