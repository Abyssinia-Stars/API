<?php

namespace App\Http\Controllers;

use App\Models\PaymentInfo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class PaymentInfoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $user = Auth::user();

        $validated = Validator::make($request->all(), [
            'first_name' => 'required|string',
            'last_name' => 'required|string',
            'email' => 'required|email',
            'currency' => 'required|in:USD,ETB',
            'phone_number' => 'required|string',
        ]);

        if ($validated->fails()) {
            return response()->json([
                'message' => 'Bad Request',
                'errors' => $validated->errors()
            ], 400);
        }

        // Ensure the authenticated user exists
        if (!$user) {
            return response()->json([
                'message' => 'Unauthorized',
            ], 401);
        }

        $data = $validated->validated();
        $data['user_id'] = $user->id; // Assign the authenticated user's ID

        $response = PaymentInfo::create($data);
        return response()->json($response, 201);
    }

    /**
     * Display the specified resource.
     */
    public function getPaymentInfo()
    {
        $user_id = Auth::user()->id;
        $payment_info = PaymentInfo::where('user_id', $user_id)->firstOrFail();
        return response()->json($payment_info);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, PaymentInfo $paymentInfo)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(PaymentInfo $paymentInfo)
    {
        //
    }
}
