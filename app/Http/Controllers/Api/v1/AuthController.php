<?php

namespace App\Http\Controllers\Api\v1;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class AuthController
{
    public function login(Request $request)
    {
        $data = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
            'device_name' => 'nullable|string'
        ]);

        $user = User::where('email', $data['email'])->first();
        if (!$user || !Hash::check($data['password'], $user->password)) {
            throw ValidationException::withMessages(['email' => __('auth.failed')]);
        }

        $request->headers->set('X-Request-Id', $request->header('X-Request-Id') ?? Str::uuid()->toString());

        // Access token
        $token = $user->createToken($data['device_name'] ?? 'api')->plainTextToken;

        // Refresh token (DB table refresh_tokens)
        $refresh = Str::random(64);
        DB::table('refresh_tokens')->insert([
            'user_id' => $user->id,
            'token' => hash('sha256', $refresh),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return response()->json([
            'token' => $token,
            'refresh_token' => $refresh,
            'user' => $user
        ]);
    }

    public function refresh(Request $request)
    {
        $request->validate(['refresh_token' => 'required|string']);
        $hashed = hash('sha256', $request->input('refresh_token'));

        $row = DB::table('refresh_tokens')->where('token', $hashed)->first();
        if (!$row) {
            return response()->json(['message' => 'Invalid refresh token'], 401);
        }

        $user = User::find($row->user_id);
        $token = $user->createToken('api')->plainTextToken;

        return response()->json(['token' => $token]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['ok' => true]);
    }

    public function me(Request $request)
    {
        return response()->json($request->user());
    }

    public function updateMe(Request $request)
    {
        $request->validate([
            'name' => 'sometimes|string|max:255',
            'locale' => 'sometimes|in:fa,en'
        ]);
        $user = $request->user();
        $user->update($request->only('name', 'locale'));
        return response()->json($user);
    }

    public function updatePassword(Request $request)
    {
        $data = $request->validate([
            'current_password' => 'required',
            'password' => 'required|min:8|confirmed'
        ]);

        if (!Hash::check($data['current_password'], $request->user()->password)) {
            throw ValidationException::withMessages(['current_password' => __('passwords.mismatch')]);
        }
        $request->user()->update(['password' => Hash::make($data['password'])]);
        return response()->json(['ok' => true]);
    }
}
