<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

use App\Models\ArtistProfile;
use App\Models\Subscription;


class CheckSubscription 
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
        $artistProfiles = ArtistProfile::all();
        foreach ($artistProfiles as $artistProfile) {
           if($artistProfile->is_subscribed){
               $subscription = Subscription::where('user_id', $artistProfile->user_id)->first();
               if($subscription->ends_at < now()){
                   $artistProfile->is_subscribed = false;
                   $artistProfile->save();
               }
           }
        }
    }
}
