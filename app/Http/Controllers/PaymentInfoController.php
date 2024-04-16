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
            'account_name' => 'required|string',
            'account_number' => 'required|string',
            'bank_code' => 'required|string'
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

        if(PaymentInfo::where('user_id', $user->id)->exists()){
            return response()->json([
                'message' => 'Payment info already exists',
            ], 400);
        }
        $response = PaymentInfo::create($data);
        //send the response as an object and not an arrat
        
        return response()->json($response, 201);
    }

    /**
     * Display the specified resource.
     */
    public function getPaymentInfo()
    {
        $user_id = Auth::user()->id;
        $payment_info = PaymentInfo::where('user_id', $user_id)->get();

       //return the payment info as an Array 
        return response()->json($payment_info, 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request)
    {
        //

        $user = Auth::user();

        $validated = Validator::make($request->all(), [
            'first_name' => 'string',
            'last_name' => 'string',
            'email' => 'email',
            'currency' => 'in:USD,ETB',
            'phone_number' => 'string',
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

        if(!PaymentInfo::where('user_id', $user->id)->exists()){
            return response()->json([
                'message' => 'Payment info does not exist',
            ], 400);
        }

        $payment_info = PaymentInfo::where('user_id', $user->id)->first();
        $payment_info->update($data);

        return response()->json($payment_info, 201);


    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(PaymentInfo $paymentInfo)
    {
        //
        $user = Auth::user();

        if (!$user) {
            return response()->json([
                'message' => 'Unauthorized',
            ], 401);
        }

        if(!PaymentInfo::where('user_id', $user->id)->exists()){
            return response()->json([
                'message' => 'Payment info does not exist',
            ], 400);
        }

        $payment_info = PaymentInfo::where('user_id', $user->id)->first();
        $payment_info->delete();

        return response()->json([
            'message' => 'Payment info deleted successfully',
        ], 200);
    }
}
