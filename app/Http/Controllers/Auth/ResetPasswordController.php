<?php

namespace App\Http\Controllers\Auth;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;

use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
//import validator 
use Illuminate\Support\Facades\Validator;


class ResetPasswordController extends Controller
{
    //
   

    public function forgotPassword(Request $request){
        $request->validate(['email' => 'required|email']);
 
        $status = Password::sendResetLink(
            $request->only('email')
        );
     
        // error_log($status);
        return $status === Password::RESET_LINK_SENT
                    ? response()->json(['message' => __($status)], 200)
                    : response()->json(['message' => __($status)], 401);
    }

    public function resetPassword (Request $request) {
        // $out = new \Symfony\Component\Console\Output\ConsoleOutput();
  
        $validator = Validator::make($request->all(), [ 
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json(['error'=>$validator->errors()], 401);
            // $out->writeln("I AM HEREE");
        }
        // $out->writeln($request->only("token"));
        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user, string $password) {
                $user->forceFill([
                    'password' => Hash::make($password)
                    ])->setRememberToken(Str::random(60));
                    
                    $user->save();
                    
                    event(new PasswordReset($user));
                }
            );
            // $out->writeln(Password::PASSWORD_RESET);
     
        return $status === Password::PASSWORD_RESET
                    ? response()->json(['message' => __($status)], 200)
                    : response()->json(['message' => __($status)], 401);
    }



}
