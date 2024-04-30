<?php

namespace App\Http\Middleware;

use App\Models\Subscription;
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
        $subscription = Subscription::where('user_id', $user_id)
            ->where('active', true)
            ->where('ends_at', '>', now())
            ->first();
        if ($subscription === null) {
            return response()->json(['message' => 'User has no active subscription'], 401);
        }
        return $next($request);
    }
}
