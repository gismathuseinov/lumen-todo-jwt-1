<?php


namespace App\Http\Controllers;


use App\Models\User;
use Illuminate\Http\JsonResponse;
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
            'roles' => ['required']
        ]);
        if ($validator->failed()) {
            return response()->json($validator->errors());
        } else {
            $request['roles'] = (int)$request['roles'];
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
            return response()->json([$validator->errors()], 404);
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

    public function passwordReset(int $id): JsonResponse
    {
        $defaultPassword = Hash::make(123456);
        $user = User::find($id);
        $user->update([
            'password' => $defaultPassword
        ]);
        return response()->json(['msg' => 'password reset']);
    }
}
