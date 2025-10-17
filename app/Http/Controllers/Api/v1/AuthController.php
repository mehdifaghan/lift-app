<?php

namespace App\Http\Controllers\Api\v1;

use App\Models\LoginChallenge;
use App\Models\User;
use App\Services\TwoFactor\TwoFactorService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AuthController
{
    public function __construct(private readonly TwoFactorService $twoFactor)
    {
    }

    public function login(Request $request)
    {
        $data = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
            'device_name' => 'nullable|string',
        ]);

        $user = User::where('email', $data['email'])->first();
        if (!$user || !Hash::check($data['password'], $user->password)) {
            throw ValidationException::withMessages(['email' => __('auth.failed')]);
        }

        $request->headers->set('X-Request-Id', $request->header('X-Request-Id') ?? Str::uuid()->toString());

        if ($this->twoFactor->shouldRequire2FA($user)) {
            $challenge = $this->twoFactor->startChallenge($user, (string) $request->ip(), $request->userAgent());

            return response()->json([
                'requires_two_factor' => true,
                'challenge_id' => $challenge->id,
                'expires_at' => $challenge->expires_at?->toIso8601String(),
                'max_attempts' => $challenge->max_attempts,
            ], 202);
        }

        return response()->json($this->issueTokens($user, $data['device_name'] ?? null));
    }

    public function verifyChallenge(Request $request)
    {
        $data = $request->validate([
            'challenge_id' => 'required|uuid|exists:login_challenges,id',
            'code' => 'required|string|min:4|max:6',
            'device_name' => 'nullable|string',
        ]);

        $challenge = LoginChallenge::with('user')->findOrFail($data['challenge_id']);
        if (!$challenge->user) {
            return response()->json(['message' => 'CHALLENGE_USER_NOT_FOUND'], 422);
        }
        if ($challenge->used) {
            return response()->json(['message' => 'CHALLENGE_ALREADY_VERIFIED'], 410);
        }
        if ($challenge->expires_at && now()->greaterThan($challenge->expires_at)) {
            return response()->json(['message' => 'CHALLENGE_EXPIRED'], 410);
        }
        if ($challenge->attempts >= $challenge->max_attempts) {
            return response()->json(['message' => 'CHALLENGE_LOCKED'], 423);
        }

        if (!$this->twoFactor->verify($challenge, $data['code'])) {
            $challenge->refresh();
            $remaining = max(0, $challenge->max_attempts - $challenge->attempts);

            return response()->json([
                'message' => 'INVALID_CODE',
                'remaining_attempts' => $remaining,
            ], 422);
        }

        return response()->json($this->issueTokens($challenge->user, $data['device_name'] ?? null));
    }

    public function resendChallenge(Request $request)
    {
        $data = $request->validate([
            'challenge_id' => 'required|uuid|exists:login_challenges,id',
        ]);

        $challenge = LoginChallenge::with('user')->findOrFail($data['challenge_id']);
        if (!$challenge->user) {
            return response()->json(['message' => 'CHALLENGE_USER_NOT_FOUND'], 422);
        }
        if ($challenge->used) {
            return response()->json(['message' => 'CHALLENGE_ALREADY_VERIFIED'], 410);
        }

        $challenge = $this->twoFactor->resend($challenge);

        return response()->json([
            'challenge_id' => $challenge->id,
            'expires_at' => $challenge->expires_at?->toIso8601String(),
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
            'locale' => 'sometimes|in:fa,en',
        ]);
        $user = $request->user();
        $user->update($request->only('name', 'locale'));

        return response()->json($user);
    }

    public function updatePassword(Request $request)
    {
        $data = $request->validate([
            'current_password' => 'required',
            'password' => 'required|min:8|confirmed',
        ]);

        if (!Hash::check($data['current_password'], $request->user()->password)) {
            throw ValidationException::withMessages(['current_password' => __('passwords.mismatch')]);
        }
        $request->user()->update(['password' => Hash::make($data['password'])]);

        return response()->json(['ok' => true]);
    }

    private function issueTokens(User $user, ?string $deviceName): array
    {
        $token = $user->createToken($deviceName ?: 'api')->plainTextToken;

        $refresh = Str::random(64);
        DB::table('refresh_tokens')->insert([
            'user_id' => $user->id,
            'token' => hash('sha256', $refresh),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return [
            'token' => $token,
            'refresh_token' => $refresh,
            'user' => $user,
            'requires_two_factor' => false,
        ];
    }
}
