<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Password;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;

 class AuthController extends Controller
{

   public function register(Request $request)
{
    $request->validate([
        'name' => 'required|string',
        'email' => 'required|email|unique:users',
        'password' => 'required|min:6|confirmed',
        'avatar' => 'nullable|image|mimes:jpg,jpeg,png|max:2048'
    ]);

    $avatarPath = null;

    if ($request->hasFile('avatar')) {
        $avatarPath = $request->file('avatar')->store('avatars', 'public');
    }

    $user = User::create([
        'name' => $request->name,
        'email' => $request->email,
        'password' => Hash::make($request->password),
        'avatar' => $avatarPath
    ]);

    $token = $user->createToken('auth_token')->plainTextToken;

    return response()->json([

        'user' => $user,
        'token' => $token
    ]);
}


public function login(Request $request)
{
    $request->validate([
        'email' => 'required|email',
        'password' => 'required'
    ]);

    $user = User::where('email', $request->email)->first();

    if (!$user || !Hash::check($request->password, $user->password)) {
        return response()->json([
            'message' => 'Invalid credentials'
        ], 401);
    }

    $token = $user->createToken('auth_token')->plainTextToken;

    return response()->json([
        'user' => $user,
        'token' => $token
    ]);
}



public function logout(Request $request)
{
    if ($request->user() && $request->user()->currentAccessToken()) {
        $request->user()->currentAccessToken()->delete();
    }

    return response()->json([
        'message' => 'Logged out'
    ]);
}






public function forgotPassword(Request $request)
{
    $request->validate([
        'email' => 'required|email|exists:users,email'
    ]);

    $token = Str::random(64);

    DB::table('password_reset_tokens')->updateOrInsert(
        ['email' => $request->email],
        [
            'email' => $request->email,
            'token' => Hash::make($token),
            'created_at' => Carbon::now()
        ]
    );

    return response()->json([
        'message' => 'Reset token generated successfully',
        'token' => $token,
        'email' => $request->email
    ]);
}

public function resetPassword(Request $request)
{
    $request->validate([
        'email' => 'required|email',
        'token' => 'required',
        'password' => 'required|min:6|confirmed'
    ]);

    $reset = DB::table('password_reset_tokens')
        ->where('email', $request->email)
        ->first();

    if (!$reset) {
        return response()->json([
            'message' => 'Invalid email'
        ], 400);
    }

    if (!Hash::check($request->token, $reset->token)) {
        return response()->json([
            'message' => 'Invalid token'
        ], 400);
    }

    $user = User::where('email', $request->email)->first();

    $user->password = Hash::make($request->password);
    $user->save();

    DB::table('password_reset_tokens')
        ->where('email', $request->email)
        ->delete();

    return response()->json([
        'message' => 'Password reset successfully'
    ]);
}
}
