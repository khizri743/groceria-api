<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        // 1. Check if user is logged in
        // 2. Check if user's role is 'admin'
        if ($request->user() && $request->user()->role === 'admin') {
            return $next($request); // Allowed
        }

        // If not admin, kick them out
        return response()->json([
            'message' => 'Unauthorized. Admin access required.'
        ], 403);
    }
}