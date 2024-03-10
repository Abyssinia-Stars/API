<?php


namespace App\Http\Controllers\Auth;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Otp as OtpModel;
use Ichtrojan\Otp\Otp;
use Illuminate\Notifications\Messages\MailMessage;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Mail;
use App\Mail\MyMail;

class OtpVerifyController extends Controller
{
    //
    public function verify(Request $request){
        $out = new \Symfony\Component\Console\Output\ConsoleOutput();

        $otp = (new Otp)->validate($request->email, $request->otp);

        if($otp->status){
    
            $user = User::where('email', $request->email)->first();
            $user->markEmailAsVerified();
            return response()->json(['message' => 'OTP verified successfully'], 200);
        }
        return response()->json(['error' => 'Invalid OTP'], 400);
        // $otp = (new Otp)->validate('michael@okoh.co.uk', '282581');
    }

    public function resendOtp(Request $request){

        $user = OtpModel::where('identifier', $request->email)->latest()->get()->first();

        // $out = new \Symfony\Component\Console\Output\ConsoleOutput();
        // $out->writeln($user->token);
        if($user){
            // $user->delete();
            // $isOtpValid = (new Otp)->validate($user->identifier, $user->token);
            if($user->valid == 1){
                // $user->delete();
                return response()->json(['error' => 'Please Check your Email! Otp has already been sent'], 400);
            }
            else{

                $user->delete();
                $otp = (new Otp)->generate($request->email, 'numeric', 4, 60);
                try{
                    Mail::to($request->email)->send(new MyMail($otp->token));
                     return response()->json(['message' => 'OTP sent successfully again'], 200);
         
                }catch(\Exception $e){
                    return response()->json(['error' => "Email Not Sent"], 400);
                }
                // $user->delete();
            }
                    
        }

        $otp = (new Otp)->generate($request->email, 'numeric', 4, 60);
        try{
            Mail::to($request->email)->send(new MyMail($otp->token));
             return response()->json(['message' => 'OTP sent successfully again'], 200);
 
        }catch(\Exception $e){
            return response()->json(['error' => "Email Not Sent"], 400);
        }
        return response()->json(['message' => 'OTP sent successfully'], 200);

    }
}
