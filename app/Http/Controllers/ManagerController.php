<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Notification;
use App\Models\User;
use App\Models\Manager;
use App\Models\ArtistProfile;
use App\Models\Offer;

use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use App\Events\RequestEvent;

class ManagerController extends Controller
{
    //

    public function sendRequest($userId)
    {
      
        $user = User::where('id', $userId)->first();
        $sender = Auth::user();
        if($user->role != 'artist'){
            return response()->json([
                'message' => 'User is not an artist'
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

}
