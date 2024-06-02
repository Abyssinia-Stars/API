<?php

namespace App\Http\Controllers\Auth;
use App\Http\Controllers\Controller;

use App\Models\User;
use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Facades\Auth;
use App\Providers\RouteServiceProvider;
use App\Models\ArtistProfile;
use App\Models\Manager;
use Illuminate\Support\Facades\Log;

class GoogleLoginController extends Controller
{
    // public function redirectToGoogle()
    // {
    //     return Socialite::driver('google')->redirect();
    // }

    public function handleGoogleCallback(Request $request)
    {
        $out = new \Symfony\Component\Console\Output\ConsoleOutput();

        
        try{
            $googleUser = Socialite::driver('google')->userFromToken($request->get("token"));
      
       
            $user = User::where('email', $googleUser->email)->first();
            $profile = null;
            if(!$user)
            {
                $user_name = $googleUser->name . str_pad(mt_rand(1,99999999),8,'0',STR_PAD_LEFT);
                $user = User::create(['name' => $googleUser->name,'user_name'=>$user_name, 'email' => $googleUser->email,'profile_picture'=>$googleUser->avatar, 'password' => \Hash::make(rand(100000,999999))]);
                $user->markEmailAsVerified();

            }
            
            if($user->role == "artist")
            {

                $profile = ArtistProfile::where('user_id', $user->id)->first();

            }
            if($user->role == "manager")
            {
                $profile = Manager::where('user_id', $user->id)->first();
            }
            return response()->json(['user' => $user,'profile' => $profile,  'access_token' => $user->createToken('authToken')],200);
           
        }catch(\Exception $e){
         
           
            return response()->json(['error' => $e->getMessage()], 400);
        }

        
        // Auth::login($user);

        // return redirect(RouteServiceProvider::HOME);
    }
}