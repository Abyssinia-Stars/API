<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

use App\Models\Subscription;
use App\Models\User;
use App\Mail\MailMessage;
use Illuminate\Support\Facades\Mail;
use App\Mail\SendReminder;
use App\Notifications\CustomResetPassword;
use Illuminate\Support\Facades\Log;
use App\Models\Notification;
use App\Events\VerifyIdEvent;



class SubscriptionReminder 
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        //
        $subscriptions = Subscription::all();
        Log::info('Checking subscriptions');
        foreach ($subscriptions as $subscription) {

            if (true) {
                $user = User::find($subscription->user_id);
             
                Log::info('Sending subscription reminder to user: ' . $user->email);
                try {
                    // Mail::raw('Hi, welcome user!', function ($message) {
                    //     $message->to($user->email)
                    //       ->subject('Your Subscription is about to expire')
                    //       ->setBody('<h1>Hi, welcome user!</h1>', 'text/html');
                    //   });
                    // Mail::to($user->email)->send(new MailMessage)->line('Your Subscription is about to expire');
                    $notification = new Notification([
                        'user_id' => $user->id,
                        'notification_type' => 'system',
                        'source_id' => 1,
                        'message' =>'Subscription is About to Expire in 3 Days' ,
                        'title' => 'Subscription Reminder',
                        'status' => 'unread'
                    ]);
                    $notification->save();
                    broadcast(new VerifyIdEvent($notification));
                    return;
                } catch (\Throwable $th) {
                    Log::info('Error sending subscription reminder to user: ' . $th->getMessage());
                    Log::error('Error sending subscription reminder to user: ' . $th->getMessage());
                    //throw $th;
                }


            }
        }
    }
}
