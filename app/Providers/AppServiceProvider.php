<?php

namespace App\Providers;

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
            $user->balance()->create(['balance' => 0, 'user_id' => $user->id, 'onhold_balance' => 0]); // Assuming you have an 'amount' column in your balances table
        });
    }
}
