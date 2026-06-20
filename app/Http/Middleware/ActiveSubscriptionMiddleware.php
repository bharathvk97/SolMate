<?php
// app/Http/Middleware/ActiveSubscriptionMiddleware.php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class ActiveSubscriptionMiddleware
{
    public function handle(Request $request, Closure $next): mixed
    {
        $user = $request->user();

        if ($user && $user->isOwner() && !$user->hasActiveSubscription()) {
            return response()->json([
                'status'  => false,
                'message' => 'Your subscription has expired. Please renew to continue using owner features.',
                'code'    => 'SUBSCRIPTION_EXPIRED',
                'redirect'=> '/api/v1/plans',
            ], 402);
        }

        return $next($request);
    }
}
