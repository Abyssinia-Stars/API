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
        $user_id = Auth::user()->id;
        $artistProfile = ArtistProfile::where('user_id', $user_id)->first();
        if (!$artistProfile->is_subscribed) {
            return response()->json(['error' => 'Artist not subscribed'], 400);
        }
        return $next($request);
    }
}
