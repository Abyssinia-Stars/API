<?php

namespace App\Http\Controllers;

use App\Notifications\IdVerified;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    public function subscribe(Request $request)
    {
        Auth::user()->updatePushSubscription(
            $request->input('endpoint'),
            $request->input('keys.p256dh'),
            $request->input('keys.auth'),
            $request->input('contentEncoding', 'aesgcm')
        );
    }

    public function unsubscribe(Request $request)
    {
        return Auth::user()->deletePushSubscription($request->endpoint);
    }

    public function send()
    {
        Auth::user()->notify(new IdVerified());
    }
}
