<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

use App\Models\ArtistProfile;
use Illuminate\Support\Facades\Log;

class AddOfferPoints 
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

        Log::info('Adding offer points to artist profiles');
        Log::info('Artist profiles: ' . $artistProfiles);
        foreach ($artistProfiles as $artistProfile) {
         $artistProfile->offfer_point += 10;
            $artistProfile->save();
        }
    }
}
