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
        $notifications = Notification::where('user_id', $id)->where('status', '!=', 'deleted')
        ->get();
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
        $notification->update(['status' => $status]);
   

        return response()->json(['Notification' => $notification]);
    }


}
