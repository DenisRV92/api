<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
//        dd(auth()->user()->getRole->name);
        $userRole = auth()->user()->getRole->name;

        if ($request->isMethod('post') && $userRole == 'user') {
            return $next($request);
        } elseif (($request->isMethod('get')
                || $request->isMethod('put'))
            && $userRole == 'manager') {
            return $next($request);
        } else {
            return response()->json([
                'message' => 'Access is denied. The wrong role'
            ], 403);
        }
    }
}
