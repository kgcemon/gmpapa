<?php

namespace App\Http\Controllers\api\auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Google_Client;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

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

    public function loginWithGoogleToken(Request $request): JsonResponse
    {
        $idToken = $request->input('token');
        $client = new Google_Client(['client_id' => env('GOOGLE_CLIENT_ID')]);

        try {
            $payload = $client->verifyIdToken($idToken);
            if ($payload) {
                $name = $payload['name'] ?? 'Unknown';
                $email = $payload['email'] ?? null;
                $avatar = $payload['picture'] ?? null;

                if (!$email) {
                    return response()->json([
                        'error' => true,
                        'message' => 'Email not found in Google token'
                    ], 400);
                }

                $user = User::firstOrCreate(
                    ['email' => $email],
                    [
                        'name' => $name,
                        'email' => $email,
                        'image' => $avatar,
                        'password' => bcrypt(Str::random(10)),
                    ]
                );

                $token = $user->createToken('API Token')->plainTextToken;

                return response()->json([
                    'error' => false,
                    'message' => 'Login successful',
                    'token' => $token,
                    'user' => $user,
                ]);
            } else {
                return response()->json([
                    'error' => true,
                    'message' => 'Invalid Google token'
                ], 400);
            }
        } catch (\Exception $e) {
            return response()->json([
                'error' => true,
                'message' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
            ], 400);
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

    public function profileUpdate(Request $request): JsonResponse
    {
        $validate = $request->validate([
            'name' => 'required',
            'phone' => 'required|min:11|max:13'
        ]);

        $user =  $request->user();
        $user->name = $request['name'];
        $user->phone = $request['phone'];
        $user->password = Hash::make($request['password']);
        $user->save();
        return response()->json([
            'status' => true,
            'user' => $user,
            'message' => 'Profile updated successfully',
        ]);

    }

    public function mobileNumberUpdate(Request $request): JsonResponse
    {
        $validate = $request->validate([
            'mobile' => 'required|min:11|max:13',
        ]);

        $user = $request->user();
        $user->phone = $validate['mobile'];
        $user->save();
        return response()->json([
            'status' => true,
            'message' => 'Mobile number updated successfully',
        ]);
    }
}
