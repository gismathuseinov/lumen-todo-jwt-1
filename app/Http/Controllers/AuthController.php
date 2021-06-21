<?php


namespace App\Http\Controllers;


use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function store(Request $request): \Illuminate\Http\JsonResponse
    {

        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'min:3'],
            'email' => ['required', 'email', 'unique:users'],
            'password' => ['required', 'min:6'],
            'roles' => ['required', 'integer']
        ]);


        if ($validator->fails()) {
            return response()->json($validator->errors());
        } else {
            $request['password'] = Hash::make($request['password']);
            $user = User::create($request->all());
            return response()->json([
                'user' => $user
            ]);
        }

    }

    public function login(Request $request): \Illuminate\Http\JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => ['required', 'email', 'unique:users'],
            'password' => ['required', 'min:6'],
        ]);
        if ($validator->failed()) {
            return response()->json([$validator->errors()]);
        } else {

            $credentials = $request->only(['email', 'password']);
            if (!$token = Auth::attempt($credentials)) {
                return response()->json([
                    'msg' => 'unAuthorized'
                ], 401);
            }
            return $this->respondWithToken($token);
        }


    }
}
