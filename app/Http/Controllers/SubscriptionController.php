<?php

namespace App\Http\Controllers;

use App\Models\Plans;
use App\Models\Subscription;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class SubscriptionController extends Controller
{

    public function subscribe(Request $request)
    {
        $validation = Validator::make($request->all(), [
            'plan_id' => 'integer'
        ]);

        if ($validation->fails()) {
            return response()->json(['error' => $validation->errors()], 400);
        }

        $plan = Plans::findOrFail($request->get('plan_id'));
        $user = Auth::user();
        $balance = $user->balance->balance;
        Log::info($balance);
        if ($balance < $plan->price) {
            return response()->json(['error' => 'Insufficient balance'], 400);
        }
        $user->balance()->update([
            'balance' => $balance - $plan->price,
        ]);

        $prev_end_date = now();
        if ($user->subscription) {
            $prev_end_date = $user->subscription->ends_at;
        }

        $subs = $user->subscription->updateOrCreate([
            'user_id' => $user->id,
            'plan_id' => $plan->id,
            'starts_at' => $prev_end_date,
            'ends_at' => now()->addMonths($plan->duration),
            'active' => true,
        ]);
        return $subs;
    }

    public function checkSubscriptionStatus()
    {
        $user_id = Auth::user()->id;
        $subscription = Subscription::where('user_id', $user_id)
            ->where('active', true)
            ->where('ends_at', '>', now())
            ->firstOrFail();

        return $subscription;
    }
}
