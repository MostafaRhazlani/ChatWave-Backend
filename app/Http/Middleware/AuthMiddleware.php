<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\Person;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\User;
use Symfony\Component\HttpFoundation\Response;

class AuthMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->header('Authorization');
        $token = substr($token, 7);

        if(!$token) {
            return response()->json(['message' => 'Unauthorized: No Token Provided'], 401);
        }

        $user = Person::where('token', hash('sha256', $token))->first();

        if (!$user) {
            return response()->json(['message' => 'Unauthorized: Invalid Token'], 401);
        }

        $request->setUserResolver(function () use ($user) {
            return $user;
        });

        return $next($request);
    }
}
