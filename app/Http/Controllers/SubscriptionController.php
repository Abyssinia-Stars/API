<?php

namespace App\Http\Controllers;

use App\Models\PaymentInfo;
use App\Models\Plans;
use App\Models\Subscription;
use Chapa\Chapa\Facades\Chapa as Chapa;
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
        return $this->initSubscription($plan);
    }

    private function initSubscription(Plans $plans)
    {
        $reference = Chapa::generateReference();
        $user = Auth::user();
        $payment_info = PaymentInfo::where('user_id', $user->id)->firstOrFail();

        if ($payment_info === null) {
            return response()->json([
                'message' => 'Please add payment information'
            ], 400);
        }

        // Enter the details of the payment
        $data = [
            'amount' => $plans->price / 0.965, // 3.5% charge
            'email' => $user->email,
            'tx_ref' => $reference,
            'currency' => $payment_info->currency,
            'callback_url' => route('subscription_callback', [$reference, 'user_id' => $user->id, 'plan_id' => $plans->id]),
            'first_name' => $payment_info->first_name,
            'last_name' => $payment_info->last_name,
            "customization" => [
                "title" => 'Abysinia Stars',
                "description" => "Where talents shine"
            ]
        ];

        $payment = Chapa::initializePayment($data);

        if ($payment['status'] !== 'success') {
            return response()->json([
                'message' => 'Something went really bad'
            ], 500);
        }

        return $payment['data'];
    }

    public function callback($reference, Request $request)
    {
        Log::info("Recieved subscription request");
        $data = Chapa::verifyTransaction($reference);
        $user_id = $request->input('user_id');
        $plan_id = $request->input('plan_id');
        if ($data['status'] == 'success') {
            $plan = Plans::findOrFail($request->get('plan_id'));
            Subscription::updateOrCreate([
                'user_id' => $user_id,
                'plan_id' => $plan_id,
                'starts_at' => now(),
                'ends_at' => now()->addMonths($plan->duration),
                'active' => true,
            ]);

            return response()->json(['error' => 'Successfully accepted payment']);
        } else {
            return response()->json(['error' => 'Payment faild'], 500);
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
