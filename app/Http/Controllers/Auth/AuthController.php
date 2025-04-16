<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\AuthLoginRequest;
use App\Http\Requests\AuthRegisterRequest;;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request as HttpRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

;

class AuthController extends Controller
{
 
    public function register(AuthRegisterRequest $request)
    {
        $credentials = $request->validated();

        $user = User::create($credentials);
        $token = Auth::attempt($credentials);

        return $this->respondWithToken($token, $user, 'User registered successfully', 201);
    }
    
    public function login(AuthLoginRequest $request)
    {
        $credentials = $request->only('email', 'password');

        if (!$token = Auth::attempt($credentials)) {
            return response()->json(['error' => 'Invalid Credentials'], 401);
        }
        
        $user = Auth::user();
        if (!$user instanceof User) {
            return response()->json(['error' => 'User not found'], 404);
        }
        
        $token = Auth::attempt($credentials);

        return $this->respondWithToken($token, $user, 'User logged successfully', 200);
    }

    public function logout()
    {
        Auth::logout();

        return response()->json(['message' => 'Successfully logged out'], 200);
    }

    public function me()
    {
        return response()->json(new UserResource(Auth::user()), 200);
    }

    private function respondWithToken($token, User $user, $message = 'Success', $code = 200)
    {
        return response()->json([
            'status' => 'success',
            'message' => $message,
            'user' => new UserResource($user),
            'authorization' => [
                'token' => $token,
                'type' => 'bearer',
            ],
        ], $code);
    }
}
