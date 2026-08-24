<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\SignupRequest;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\UpdateUserRequest;

use App\Models\User;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    public function signup(SignupRequest $request)
    {
        $user = User::create($request->validated());
        return response()->json(['user' => $user,], 201);
    }

    public function auth(LoginRequest $request)
    {
        if (!Auth::attempt($request->validated())) {
            return response()->json([
                'message' => 'Invalid credentials.',
            ], 401);
        }

        $request->session()->regenerate();

        return response()->json(['user' => $request->user(),]);
    }

    public function logout(Request $request)
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json([
            'message' => 'Logged out successfully.',
        ]);
    }

    public function update(UpdateUserRequest $request){
        $user = $request->user();

        $user->update(['name' => $request->validated('name'),]);
        return response()->json(['user' => $user->fresh(),]);
    }

    public function validateEmail(Request $request)
    {
        $request->validate(['email' => ['required', 'email'],]);
        $exists = User::where('email', strtolower(trim($request->email)))->exists();
        
        return response()->json(['exists' => $exists,]);
    }
}