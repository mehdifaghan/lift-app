<?php

use App\Models\LoginChallenge;
use App\Models\SmsSetting;
use App\Models\TwoFactorSetting;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use function Pest\Laravel\postJson;

it('returns tokens immediately when two factor is disabled', function () {
    $user = User::factory()->create([
        'email' => 'plain@example.com',
        'password' => Hash::make('password'),
    ]);

    $response = postJson('/api/auth/login', [
        'email' => $user->email,
        'password' => 'password',
        'device_name' => 'postman',
    ]);

    $response->assertOk();
    $response->assertJsonStructure([
        'token',
        'refresh_token',
        'user',
        'requires_two_factor',
    ]);
    expect($response->json('requires_two_factor'))->toBeFalse();
});

it('requires and verifies a two factor challenge', function () {
    Http::fake([
        '*' => Http::response(['entries' => [['messageid' => 'msg_1']]], 200),
    ]);

    TwoFactorSetting::current()->update([
        'is_enabled' => true,
        'require_for_all_roles' => true,
        'max_attempts' => 3,
        'code_expiry_minutes' => 5,
    ]);

    SmsSetting::current()->update([
        'provider' => 'Kavenegar',
        'api_key' => 'fake-key',
        'sender' => '3000',
        'is_active' => true,
    ]);

    $user = User::factory()->create([
        'email' => 'twofactor@example.com',
        'password' => Hash::make('password'),
    ]);

    $login = postJson('/api/auth/login', [
        'email' => $user->email,
        'password' => 'password',
        'device_name' => 'cli',
    ]);

    $login->assertStatus(202);
    $challengeId = $login->json('challenge_id');
    expect($login->json('requires_two_factor'))->toBeTrue();

    $challenge = LoginChallenge::findOrFail($challengeId);
    $challenge->update(['code_hash' => Hash::make('654321')]);

    $failed = postJson('/api/auth/login/verify', [
        'challenge_id' => $challengeId,
        'code' => '123123',
    ]);

    $failed->assertStatus(422);
    expect($failed->json('remaining_attempts'))->toBe(2);

    $success = postJson('/api/auth/login/verify', [
        'challenge_id' => $challengeId,
        'code' => '654321',
        'device_name' => 'cli',
    ]);

    $success->assertOk();
    $success->assertJsonStructure([
        'token',
        'refresh_token',
        'user',
        'requires_two_factor',
    ]);

    expect($success->json('requires_two_factor'))->toBeFalse();
    expect(LoginChallenge::find($challengeId)->used)->toBeTrue();
});
