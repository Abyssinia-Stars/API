<?php

namespace App\Http\Controllers\Auth;
use App\Http\Controllers\Controller;

use App\Models\User;
use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Facades\Auth;
use App\Providers\RouteServiceProvider;

class GoogleLoginController extends Controller
{
    // public function redirectToGoogle()
    // {
    //     return Socialite::driver('google')->redirect();
    // }


    public function handleGoogleCallback(Request $request)
    {
        $out = new \Symfony\Component\Console\Output\ConsoleOutput();
        // $decoded = json_decode($request->getContent(), true);
        // $encoded = json_encode($decoded);
 
        // return response()->json(['msg' => $request->get("token")], 200);
        
        try{
            $googleUser = Socialite::driver('google')->userFromToken($request->get("token"));
      
            // $out->writeln("I AM H");
            $user = User::where('email', $googleUser->email)->first();
            if(!$user)
            {
                $user_name = $googleUser->name . str_pad(mt_rand(1,99999999),8,'0',STR_PAD_LEFT);
                $user = User::create(['name' => $googleUser->name,'user_name'=>$user_name, 'email' => $googleUser->email,'phone_number'=>'0990091820','profile_picture'=>$googleUser->avatar, 'password' => \Hash::make(rand(100000,999999))]);
                $user->markEmailAsVerified();
            }
            return response()->json(['user' => $user, 'access_token' => $user->createToken('authToken')],200);
           
        }catch(\Exception $e){
         
            $out->writeln($e->getMessage());
            return response()->json(['error' => $e->getMessage()], 400);
        }

        
        // Auth::login($user);

        // return redirect(RouteServiceProvider::HOME);
    }
}