<?php

namespace App\Http\Controllers\Auth;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Otp as OtpModel;
use Illuminate\Support\Facades\Auth;

class VerificationController extends Controller
{
    //
    public function notice(Request $request)
    {
        return $request->user()->hasVerifiedEmail() 
            ? redirect()->route('login') : view('auth.verify-email');
    }

    public function verify(Request $request, $id)
    {
        if (!$request->hasValidSignature()) {
            return response()->json(["message" => "Invalid/Expired url provided."], 401);
        }

        $user = User::findOrFail($id);

        if (!$user->hasVerifiedEmail()) {
            $user->markEmailAsVerified();
            $otpuser = OtpModel::where('identifier', $user->email)->first();
            $otpuser->valid = 0;
            $otpuser->save();
            return response()->json(["message" => "Email verified."], 200);

        }
 

            return response()->json(["message" => "Already Verified."], 200);  
    }

    public function resend(Request $request)
    {
    
        $user = User::where('email', $request->email)->first();

        if ($user) {
            // Send email verification notification
            $user->sendEmailVerificationNotification();
            return response()->json(["message" => "Link Sent."], 200);
        } else {
            return response()->json(["message" => "Not sent."], 400);
        }
    }
}
