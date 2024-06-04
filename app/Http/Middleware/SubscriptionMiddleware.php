<?php

namespace App\Http\Middleware;

use App\Models\Subscription;
use App\Models\ArtistProfile;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;


class SubscriptionMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();
        if($user->role ==="artist"){

            $artistProfile = ArtistProfile::where('user_id', $user->id)->first();
            if (!$artistProfile->is_subscribed) {
                return response()->json(['error' => 'Artist not subscribed'], 400);
            }
        }
        else if($user->role==="manager"){
            $managerProfile = Manager::where('user_id', $user->id)->first();
            if (!$managerProfile->is_subscribed) {
                return response()->json(['error' => 'Manager not subscribed'], 400);
            }

        }
        return $next($request);
    }
}
