<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    function register(Request $request) {
        $request->validate([
            'name' => 'required|string',
            'email' => 'required|email|unique:users',
            'password' => 'required|string|min:6'
        ]);
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password)
        ]);
        // $token = $user->createToken('auth_token')->plainTextToken;
        // return response()->json([
        //     'access_token' => $token,
        //     'token_type' => 'Bearer'
        // ]);
        event(new Registered($user));
        return response()->json([
            'message' => 'User registered successfully'
        ]);
    }

    function login(Request $request) {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string'
        ]);
        $user = User::where('email', $request->email)->first();
        if (! $user || ! Hash::check($request->password, $user->password)) {
            return response()->json([
                'message' => 'Email or password is incorrect'
            ], 401);
        }
        $token = $user->createToken('auth_token')->plainTextToken;
        return response()->json([
            'username' => $user->name,
            'access_token' => $token,
            'token_type' => 'Bearer'
        ]);
    }

    function logout(Request $request) {
        $request->user()->tokens()->delete();
        return response()->json([
            'message' => 'Logged out'
        ]);
    }

}
