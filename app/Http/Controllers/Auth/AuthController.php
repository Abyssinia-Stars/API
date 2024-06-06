<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\ArtistProfile;
use Illuminate\Http\Request;
use App\Models\User;

use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use \Illuminate\Http;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Storage;
use App\Events\VerifyIdEvent;
use App\Models\Notification;
use App\Models\Offer;
use App\Models\Subscription;
use App\Models\Manager;

use App\Models\Work;


use Illuminate\Support\Facades\Log;




class AuthController extends Controller
{


    public function loginView()
    {
        return view('auth.login');
    }
    public function registerUser(Request $request)
    {


        $out = new \Symfony\Component\Console\Output\ConsoleOutput();

        // $out->writeln("i am here");

        $request->merge([
            'user_name' => $request->get('name') . str_pad(mt_rand(1, 99999999), 8, '0', STR_PAD_LEFT),
        ]);

        $validation = Validator::make($request->all(), [
            'name' => 'required|max:55',
            'email' => 'email|required|unique:users',
            'password' => 'required|confirmed',
            'role' => 'required|in:artist,manager,customer',
            'phone_number' => ['regex:/^(\+251|251|0)?9\d{8}$/'],
            'profile_picture' => 'image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            "user_name" => "required|unique:users"
        ]);

        //  echo $request;

        if ($validation->fails()) {
            return response()->json(['error' => $validation->errors()], 400);
        }
        $validatedData = $request->all();

        $validatedData['password'] = Hash::make($validatedData['password']);

        $user = User::create($validatedData);


        event(new Registered($user));


        return response()->json(['user' => $user, 'message' => 'User created successfully']);
    }

    public function loginUser(Request $request)
    {


        $validation = Validator::make($request->all(), [
            'email' => 'email|required',
            'password' => 'required'
        ]);

        if ($validation->fails()) {
            return response()->json(['error' => $validation->errors()], 400);
        }

        $loginData = $request->all();


        if (Auth::attempt($loginData)) {

            $user = Auth::user();
            if ($user->is_deleted === 1) {
                return response()->json(['error' => "Account Doesn't exist"], 401);
            }
            if ($user->is_active === false) {
                return response()->json(['error' => 'Account has been Deactivated! Please Contact Support'], 401);
            }

            $profile = null;

            if ($user->role === "artist") {
                $profile = ArtistProfile::where("user_id", $user->id)->first();
            }

            if ($user->role === "manager") {
                $profile = Manager::where("user_id", $user->id)->first();
            }

            if ($user->hasVerifiedEmail()) {
                $accessToken = $user->createToken('authToken')->accessToken;

                $notification = new Notification([
                    'user_id' => $user->id,
                    'notification_type' => 'system',
                    'source_id' => $user->id,
                    "title" => 'New Login',
                    'message' => 'From device : ' . $request->header('user-agent'),
                    'status' => 'unread'
                ]);

                $notification->save();

try {
    //code...
    broadcast(new VerifyIdEvent($notification));
} catch (\Throwable $th) {
    //throw $th;
    
}





                return response()->json(['user' => $user, 'access_token' => $accessToken, 'profile' => $profile]);
            } else {
                return response()->json(['error' => 'Email not verified'], 401);
            }
        }

        return response()->json(['error' => 'Invalid Credentials'], 401);
    }

    public function logout(Request $request)
    {

        $request->user()->token()->revoke();

        return response()->json(['message' => 'Successfully logged out.']);
    }

    public function uploadIdImage(Request $request)
    {
        $validation = Validator::make($request->all(), [
            'id_image' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);
        //  echo $request;
        if ($validation->fails()) {
            return response()->json(['error' => $validation->errors()], 400);
        }

        $user = Auth::user();
        if ($user->is_verified !== 'unverified') {
            return response()->json(['error' => 'Verification is pending or already verfied'], 400);
        }

        $idImagePath = Storage::url($request->file('id_image')->store('public/id_images'));

        $user->id_image = $idImagePath;
        $user->is_verified = 'pending';
        $user->save();

        $notification = new Notification([
            'user_id' => 1,
            'notification_type' => 'system',
            'source_id' => $user->id,
            'title' => "ID Verification",
            'message' => $user->name . ' wants to verify ID',
            'status' => 'unread'
        ]);
        $notification->save();

        broadcast(new VerifyIdEvent($notification));
        return response()->json(['message' => 'ID image uploaded successfully', 'user' => $user]);
    }

    public function me(){
        $user = Auth::user();
        if($user->is_active === false){
            return response()->json(['error' => 'Account has been Deactivated! Please Contact Support'], 401);
        }
        
        if ($user && $user->role == 'artist') {
            $artistProfile = ArtistProfile::where('user_id', $user->id)->firstOrFail();
            $subscriptionPlan = Subscription::where('user_id', $user->id)->first();

            if($subscriptionPlan){

                $subscriptionPlan->makeHidden('id');
            }
            $artistProfile->makeHidden('id'); $totalOffers = Offer::where('artist_id', $user->id)->count();
            $completedOffers = Offer::where('artist_id', $user->id)->where('status', 'completed')->orWhere('status', 'accepted')->count();

            
            $responseData = array_merge($user->toArray(), $artistProfile->toArray());
            if ($subscriptionPlan) {
                $responseData = array_merge($responseData, $subscriptionPlan->toArray());
            }
            $responseData = array_merge($responseData, ['total_offers' => $totalOffers, 'completed_offers' => $completedOffers]);
            if($artistProfile->manager_id !== null){
                $manager = User::where('id', $artistProfile->manager_id)->first();
                $manager->makeHidden('id');

                $responseData = array_merge($responseData, ["manager" => $manager]);
            
            }

           
        
            return response()->json($responseData);

        }
        
        if ($user && $user->role == 'manager') {
            $managerProfile = Manager::where('user_id', $user->id)->first();
            $subscriptionPlan = Subscription::where('user_id', $user->id)->first();

            if($subscriptionPlan){

                $subscriptionPlan->makeHidden('id');
            }
            if($managerProfile){
                $managerProfile->makeHidden('id'); 

                $responseData = array_merge($user->toArray(), $managerProfile->toArray());
            }
            else {
                $responseData = $user->toArray();
            }
           
            

            
            if ($subscriptionPlan) {
                $responseData = array_merge($responseData, $subscriptionPlan->toArray());
            }
            
            $artistsManagedByManager = ArtistProfile::where('manager_id', $user->id)->get("user_id","name");
            $artistProfile = [];
            foreach($artistsManagedByManager as $artist){
                $artistProfile[] = User::where('id', $artist->user_id)->get(["name","profile_picture","id","email","user_name"])->first();
                
            }
            $responseData = array_merge($responseData, ['artists_managed' => $artistProfile]);

           
        
            return response()->json($responseData);

        }
        
        if ($user && $user->role == 'customer') {
           
        
           
            $jobProfile = Work::where('client_id', $user->id)->get();
            // foreach ($jobProfile as $job) {
            //     # code...
            //     $job->makeHidden('id');
            // }
            
            $responseData = array_merge($user->toArray(), ["jobs" => $jobProfile]);
        
            return response()->json($responseData);

        }

        
        
        return response()->json($user);
        
    }
}
