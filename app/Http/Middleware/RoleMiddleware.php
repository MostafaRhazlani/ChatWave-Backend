<?php

namespace App\Http\Middleware;

use App\Models\Person;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, $role): Response
    {
        $token = $request->header('Authorization');
        $token = substr($token, 7);

        $user = Person::where('token', hash('sha256', $token))->first();
    
        if(!$user || $user['role'] !== $role) {
            return response()->json(['message' => 'You don\'t have access for this'], 403);
        }
        return $next($request);
    }
}
