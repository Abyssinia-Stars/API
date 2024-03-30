<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
class ManagerController extends Controller
{
    //

    public function sendRequest($userId)
    {
      
        $user = User::where('id', $userId)->first();
        if($user->role != 'artist'){
            return response()->json([
                'message' => 'User is not an artist'
            ], 400);
        }

        try{

            $notification = Notification::create([
                'user_id' => $user->id,
                'notification_type' => 'artist_manager',
                'source_id' => auth()->id(),
                'message' => 'Manager wants to work with you',
                'status' => 'pending'
            ]);
            
            return response()->json([
                'message' => 'Request sent successfully',
                'notification' => $notification
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

}
