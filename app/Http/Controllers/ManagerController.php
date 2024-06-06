<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Notification;
use App\Models\User;
use App\Models\Manager;
use App\Models\ArtistProfile;
use App\Models\Offer;
use App\Models\Subscription;
use App\Models\Plans;

use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use App\Events\RequestEvent;
use Illuminate\Support\Facades\Storage;


class ManagerController extends Controller
{
    //

    public function store(Request $request)
    {

        $validation = Validator::make($request->all(), [
            'profile_picture' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'bio' => 'string|max:255',
            'location' => 'string|max:255',
            'gender' => 'string|max:255',
        
        ]);

        if ($validation->fails()) {
            return response()->json(['error' => $validation->errors()], 400);
        }
        $validatedData = $request->all();

        // Upload the cover picture
        if($request->hasFile('profile_picture'))
        $profilePicturePath = Storage::url($request->file('profile_picture')->store('public/profile_pictures'));

        // Upload the attachments
 
        try {
            $user = Auth::user(); 
            // Create the ArtistProfile with the validated data
            $artistProfile = Manager::create(
                [
                    'user_id' => $user->id,
                    'bio' => $validatedData['bio'],
                    'location' =>  $validatedData['location'],
                    'gender'=>  $validatedData['gender'],
                    'is_subscribed' => 0
                ]
            );
            // Update the user's profile picture if provided in the request
            if (isset($validatedData['profile_picture'])) {
                $user->profile_picture = $profilePicturePath;
                $user->save();
                // return response()->json(['message' => 'Profile picture updated successfully']);
            }
            // Optionally, associate the user with the artist profile here
            // For example, $artistProfile->user_id = $user->id;
            // $artistProfile->save();
            return response()->json(['message' => 'Manager profile created successfully', 'manager_profile' => $artistProfile]);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error creating manager profile: ' . $e->getMessage()], 500);
        }
    }
    public function sendRequest($userId)
    {
      
        $user = User::where('id', $userId)->first();
        $sender = Auth::user();
        $manager = Manager::where('user_id', $sender->id)->first();

        if($user->role != 'artist'){
            return response()->json([
                'message' => 'User is not an artist'
            ], 400);
        }

        if($manager->is_subscribed == 0){
            return response()->json([
                'message' => 'You need to subscribe to send requests'
            ], 400);
        }

        $subscription = Subscription::where("user_id", $sender->id)->first();
        $plan = Plans::where("id", $subscription->plan_id)->first();
        $artistsManagedByManagerCount = ArtistProfile::where('manager_id', $sender->id)->count();

        if($artistsManagedByManagerCount > $plan->number_of_people){
            return response()->json([
                'message' => 'You have reached the maximum number of artists you can manage'
            ], 400);
        }

        $alreadyExists = Notification::where('user_id', $user->id)->where('source_id', auth()->id())->where('notification_type', 'request')->where('status', "!=", 'deleted')->first();
        if($alreadyExists){
            return response()->json([
                'message' => 'Request already sent'
            ], 400);
        }

        try{

            $notification = Notification::create([
                'user_id' => $user->id,
                'notification_type' => 'request',
                'source_id' => auth()->id(),
                'title' => 'Manager Request',
                'message' => $sender->name .  ' Manager wants to work with you',
                'status' => 'unread'
            ]);
            
    broadcast(new RequestEvent($notification));


            return response()->json([
                'message' => 'Request sent successfully',
                'request' => $notification
            ]);
        }
        catch(\Exception $e){
            return response()->json([
                'message' => 'An error occurred',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function handleResponse(Request $request, $notificationId){

 

        $validate = Validator::make($request->all(), [
            'status' => 'required|in:accepted,rejected'
        ]);
        

        $notification = Notification::find($notificationId);

        if(!$notification){
            return response()->json([
                'message' => 'Notification not found'
            ], 404);
        }

        if($notification->status != 'pending'){
            return response()->json([
                'message' => 'Notification has already been responded to'
            ], 400);
        }

        $notification->status = $request->status;
        $notification->save();

        return response()->json([
            'message' => 'Response sent successfully',
            'notification' => $notification
        ]);

    }

    public function getNotifications(){
        $notifications = Notification::where('user_id', auth()->user()->id())->get();
        return response()->json($notifications);
    }

    public function removeManager($id){

        $artist = Auth::user()->id;
        $artistProfile = ArtistProfile::where('user_id', $artist)->first();
        $offers = Offer::where('artist_id', $artist)->get();
        $offersCount = Offer::where('artist_id', $artist)->count();
        $areAllOffersCompleted = true;
        foreach($offers as $offer){

        if($offer->status === "accepted" || $offer->status === "pending"){
            $areAllOffersCompleted = false;
            break;
        }
     
    }
    if($areAllOffersCompleted){
        if($artistProfile->manager_id){
            $artistProfile->manager_id = null;
            $artistProfile->save();
            return response()->json(['message' => 'Manager removed successfully'], 200);
        }}
        else {
            return response()->json(['message' => "You can't remove manager! You have pending offers, Please Contact your Manager!"], 400);
        }

     
    }
    public function getManagerProfile($id){
        $manager = User::where('id', $id)->first();
        $managerProfile = Manager::where('user_id', $id)->first();
        $artistsManagedByManager = ArtistProfile::where('manager_id', $id)->get("user_id","name");
        $artistProfile = [];
        foreach($artistsManagedByManager as $artist){
            $artistProfile[] = User::where('id', $artist->user_id)->get(["name","profile_picture","id","email","user_name"])->first();
            
        }


        $manager->makeHidden('id');
        $managerProfile->makeHidden('id');
      
        return response()->json([
            'manager' => $manager,
            'manager_profile' => $managerProfile,
            'artists_managed' => $artistProfile
        ]);
    
    }

    public function pendingRequests(){
        $notifications = Notification::where("notification_type", "request")->where("status", "unread")->get();
        $artistProfile = [];

        if($notifications){

            foreach ($notifications as $notification) {
                $artistProfile[] = User::where('id', $notification->user_id)->get(["name","profile_picture","id","email"])->first();
            }

            return response()->json([
                "pending_requests" => $artistProfile
            ]);
        }


    }

    public function removeArtist($id){
        $artist = ArtistProfile::where('user_id', $id)->first();
        $user = Auth::user();
        $notification = Notification::where('user_id', $id)->where('source_id', $user->id)->where("status","accepted")->first();
        if($artist){
            $artist->manager_id = null;
            $artist->save();
            $notification->delete();
            

            return response()->json(['message'=>"success"],200);
        }
        else return response()->json(['message'=>"failed"],200);
              
    }

}
