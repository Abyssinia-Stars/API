<?php

namespace App\Http\Controllers;

use App\Models\PaymentInfo;
use App\Models\Plans;
use App\Models\Subscription;
use App\Models\Balance;
use App\Models\TxnHistory;
use App\Models\ArtistProfile;
use Chapa\Chapa\Facades\Chapa as Chapa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class SubscriptionController extends Controller
{

    public function subscribe(Request $request, $id)
    {
        $user = Auth::user();

        if($id == 0 ){
            
           $userProfile = ArtistProfile::where('user_id', $user->id)->first();
           $userProfile->is_subscribed = false;
           $userProfile->save();
           $subscription = Subscription::where('user_id', $user->id)->first();
           $subscription->starts_at = now();
              $subscription->ends_at = now()->addMonths(1);
            return response()->json(['message' => 'Subscription cancelled']);

        }
     
        $plan = Plans::findOrFail($id);
     
        return $this->initSubscription($plan);
    }

    private function initSubscription(Plans $plans)
    {
        $user = Auth::user();
       
        $balance = Balance::where('user_id', $user->id)->firstOrFail();




        if ($balance->balance < $plans->price) {
            return response()->json(['error' => 'Insufficient balance'], 400);
        }

        try {
            //code...
            // return $plans;
            $balance->balance -= $plans->price;
            $balance->save();

            Subscription::updateOrCreate([
                'user_id' => $user->id,
                'plan_id' => $plans->id,
                'starts_at' => now(),
                'ends_at' => now()->addMonths($plans->duration),
                'active' => true,
            ]);

            //create txn
            $txn = new TxnHistory([
                'tx_ref' => 'txn_' . time(), // 'txn_' . time(),
                'user_id' => $user->id,
                'amount' => $plans->price,
                'type' => 'payment',
                'charge' => $plans->price,
                'from' => $user->id,
                'to' => $user->id,
                'reason' => 'Subscription to ' . $plans->name,
            ]);
     
            $txn->save();

           $userProfile = ArtistProfile::where('user_id', $user->id)->first();
            $userProfile->is_subscribed = true;
            $userProfile->offfer_point = $userProfile->offfer_point + 100;
            $userProfile->save();



            return response()->json(['message' => 'Subscription successful']);
        } catch (\Throwable $th) {
            //throw $th;
            return response()->json(['error' => $th], 500);
        }

    }


    public function buyOffer(Request $request)
    {

        
        $validator = Validator::make($request->all(), [
        'amount' => 'required|numeric',
        'offer_point' => 'required|numeric',
        ]);



        if ($validator->fails()) {
            return response()->json([
                'message' => 'Bad Request',
                'errors' => $validator->errors()
            ], 400);
        }

        $user = Auth::user();
        $userProfile = ArtistProfile::where('user_id', $user->id)->first();
        $balance = Balance::where('user_id', $user->id)->firstOrFail();

        if ($balance->balance < $request->amount) {
            return response()->json(['error' => 'Insufficient Balance'], 400);
        }

        try {
            //code...
            $balance->balance -= $request->amount;
            $balance->save();
            $userProfile->offfer_point += $request->offer_point;
            $userProfile->save();
            $txn = new TxnHistory([
                'tx_ref' => 'txn_' . time(), // 'txn_' . time(),
                'user_id' => $user->id,
                'amount' => $request->amount,
                'type' => 'payment',
                'charge' => $request->amount,
                'from' => $user->id,
                'to' => $user->id,
                'reason' => ' Buy Offer Points ' . $request->offer_point,
            ]);
     
            $txn->save();
            return response()->json(['message' => 'Offer point purchase successful'],200);
        } catch (\Throwable $th) {
            //throw $th;
            return response()->json(['error' => $th], 500);
        }
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
