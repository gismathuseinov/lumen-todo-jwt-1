<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Laravel\Lumen\Routing\Controller as BaseController;

class Controller extends BaseController
{
    protected function respondWithToken($token): JsonResponse
    {
        return response()->json([
            'token' => $token,
            'token_type' => 'bearer',
            'expires_in' => 1024 * 60,
            'user' => \auth()->user()
        ], 200);
    }

    protected function refreshToken(): JsonResponse
    {
        return $this->respondWithToken(auth()->refresh());
    }
}
