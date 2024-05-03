<?php

namespace App\Providers;

use App\Models\Plans;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;

use App\Models\User;


class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        User::created(function ($user) {
            // Create a balance record for the user
            $user->balance()->create(['balance' => 0, 'user_id' => $user->id, 'onhold_balance' => 0]);
           
            if ($user->role == 'artist' || $user->role == 'manager') {
                $free_trail = Plans::where('name', 'free')->first();
                Log::info($free_trail->id);
                $user->subscription()->create([
                    'user_id' => $user->id,
                    'plan_id' => $free_trail->id,
                    'starts_at' => now(),
                    'ends_at' => now()->addMonths($free_trail->duration)
                ]);
            }
        });
    }
}
