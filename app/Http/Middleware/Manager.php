<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class Manager
{
    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $auth = Auth::user();
        if (!$auth) {
            return response()->json([
               'msg' => 'unAuthorized'
            ], 401);
        } else {
            if ($auth['roles'] === \Roles::MANAGER || $auth['roles'] === \Roles::ADMIN) {
                return $next($request);
            } else {
                return response()->json([
                    'msg' => 'Access Denied'
                ], 400);
            }
        }


    }
}
