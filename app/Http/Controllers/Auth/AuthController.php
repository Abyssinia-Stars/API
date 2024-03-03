<?php

namespace App\Http\Controllers\Auth;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use \Illuminate\Http;
use Illuminate\Support\Facades\Validator;
use Illuminate\Auth\Events\Registered;

class AuthController extends Controller
{

    
    public function loginView() {
        return view('auth.login');
    }
    public function registerUser(Request $request){


        $validation = Validator::make($request->all() ,[
            'name' => 'required|max:55',
            'email' => 'email|required|unique:users',
            'password' => 'required|confirmed',
            'role' => 'required|in:artist,manager,customer',
            'phone_number' => ['required','regex:/^(\+251|251|0)?9\d{8}$/'],
            'profile_picture' => 'optional|image|mimes:jpeg,png,jpg,gif,svg|max:2048'
         ]);
         
         
        //  echo $request;

        if($validation->fails()) {
            return response()->json(['error' => $validation->errors()], 400);
         
        }
        $validatedData = $request->all();
      
        $validatedData['password'] = Hash::make($validatedData['password']);

        $user = User::create($validatedData);

        event(new Registered($user));
        $accessToken = $user->createToken('authToken')->accessToken;

        return response()->json(['user' => $user, 'access_token' => $accessToken]);



    }

    public function loginUser(Request $request) {
       
        $loginData = $request->validate([
            'email' => 'email|required',
            'password' => 'required'
        ]);

        if(!auth()->attempt($loginData)) {
            return response()->json(['message' => 'Invalid Credentials']);
        }

        $accessToken = auth()->user()->createToken('authToken')->accessToken;

        return response()->json(['user' => auth()->user(), 'access_token' => $accessToken]);

    }
    //
}
