<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class NotificationController extends Controller
{

    public function index()
    {
       
        $id = Auth::user()->id;

        //order notfications from new to old 


        $notifications = Notification::where('user_id', $id)->orderBy('created_at', 'desc')
        ->get();

        //only return notifications that have status accepted if it has not expired give it 1minute after being accepted to expire
        $notifications = $notifications->filter(function($notification){
            return $notification->status == 'unread';
        });



        return response()->json(['Notifications' => $notifications]);
    }

    public function show($id)
    {
        $notification = Notification::find($id);
        return response()->json(['Notification' => $notification]);
    }

    public function update($id,$status)
    {

        $user_id = Auth::user()->id;
        
        $notification = Notification::where('user_id', $user_id)->where("id", $id)->first();
        
        $notification->status = $status;
        $notification->save();

      
    }


}
