<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, string ...$roles): mixed
    {
        $user = $request->user();
        if (!$user || !in_array($user->role, $roles)) {
            if ($request->expectsJson()) {
                return response()->json(['status'=>false,'message'=>'Unauthorized. Insufficient permissions.'], 403);
            }
            return redirect()->route('home')->with('error', 'Unauthorized access.');
        }
        return $next($request);
    }
}
