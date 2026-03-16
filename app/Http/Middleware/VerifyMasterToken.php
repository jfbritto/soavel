<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class VerifyMasterToken
{
    public function handle(Request $request, Closure $next)
    {
        $token = $request->header('X-Master-Token');
        $expectedToken = config('services.master.token');

        if (!$token || !$expectedToken || $token !== $expectedToken) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        return $next($request);
    }
}
