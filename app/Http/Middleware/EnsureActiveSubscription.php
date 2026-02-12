<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureActiveSubscription
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user instanceof User) {
            return response()->json([
                'message' => 'Unauthenticated.',
            ], 401);
        }

        $subscription = $user->subscriptions()
            ->active()
            ->latest('ends_at')
            ->first();

        if (! $subscription || ! $subscription->isActive()) {
            return response()->json([
                'message' => 'An active subscription is required to access this resource.',
            ], 403);
        }

        return $next($request);
    }
}
