<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class Admin
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $auth = Auth::user();
        if (!$auth) {
            return response()->json([
                'msg' => 'unAuthorized'
            ], 401);
        } else {
            if ($auth['roles'] === \Roles::ADMIN) {
                return $next($request);
            } else {
                return response()->json([
                    'msg' => 'Access Denied'
                ], 400);
            }
        }
    }
}
