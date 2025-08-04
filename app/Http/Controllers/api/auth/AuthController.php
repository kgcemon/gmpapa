<?php

namespace App\Http\Controllers\api\auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');
        $user = User::where('email', $credentials['email'])->first();
        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'User does not exist'
            ]);
        }
        if (!Hash::check($credentials['password'], $user->password)) {
            return response()->json([
                'status' => false,
                'message' => 'Wrong password'
            ]);
        }

        $token = $user->createToken('auth_token')->plainTextToken;
        return response()->json([
            'status' => true,
            'token' => $token,
            'user' => $user
        ]);
    }

    public function register(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required',
                'email' => 'required|email|unique:users',
                'phone' => 'required|min:11|max:13',
                'password' => 'required|min:4',
            ]);

            $user = User::create([
                'name' => $request['name'],
                'email' => $request['email'],
                'phone' => $request['phone'],
                'password' => Hash::make($request['password']),
            ]);

            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'status' => true,
                'token' => $token,
                'user' => $user,
            ]);
        }catch (\Exception $exception){
            return response()->json([
                'status' => false,
                'message' => $exception->getMessage(),
            ]);
        }
    }

    public function user(Request $request)
    {
        $user =  $request->user();
        return response()->json([
            'status' => true,
            'user' => $user,
        ]);
    }
}
