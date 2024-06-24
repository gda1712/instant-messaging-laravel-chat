<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ValidateUserHasPublicKey
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Validate that the user has a public key saved into DB
        if (is_null($request->user()->public_key)) {
            return response()->json([
                'success' => false,
                'data'    => [],
                'message' => 'User does not have a public key saved. Please save your public key first.',
            ], 400);
        }
        return $next($request);
    }
}
