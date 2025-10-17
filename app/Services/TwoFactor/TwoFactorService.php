<?php
// app/Services/TwoFactor/TwoFactorService.php
namespace App\Services\TwoFactor;

use App\Models\{LoginChallenge, TwoFactorSetting, SmsSetting, User};
use App\Services\Sms\SmsFactory;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Carbon;

class TwoFactorService {
  public function shouldRequire2FA(User $user): bool {
    $cfg = TwoFactorSetting::current();
    if (!$cfg->is_enabled) return false;
    if ($cfg->require_for_all_roles) return true;
    $exempt = $cfg->exempted_roles ?? [];
    return !in_array($user->role ?? $user->system_role, $exempt, true);
  }

  public function startChallenge(User $user, string $ip, ?string $ua): LoginChallenge {
    $cfg = TwoFactorSetting::current();
    $ttl = max(1, min(10, (int)$cfg->code_expiry_minutes));
    $max = max(3, min(5, (int)$cfg->max_attempts));

    $code = str_pad((string)random_int(0, 999999), 6, '0', STR_PAD_LEFT);
    $challenge = LoginChallenge::create([
      'user_id' => $user->id,
      'code_hash' => Hash::make($code),
      'expires_at' => now()->addMinutes($ttl),
      'max_attempts' => $max,
      'attempts' => 0,
      'ip' => $ip,
      'user_agent' => mb_substr((string)$ua, 0, 255),
    ]);

    $this->sendCode($user, $code);
    return $challenge;
  }

  public function verify(LoginChallenge $challenge, string $code): bool {
    if ($challenge->used) return false;
    if (Carbon::now()->greaterThan($challenge->expires_at)) return false;
    if ($challenge->attempts >= $challenge->max_attempts) return false;

    $challenge->attempts++;
    $challenge->save();

    if (!Hash::check($code, $challenge->code_hash)) return false;

    $challenge->used = true;
    $challenge->save();
    return true;
  }

  public function resend(LoginChallenge $challenge): LoginChallenge {
    // ساده: کد جدید + تمدید TTL
    $cfg = TwoFactorSetting::current();
    $ttl = max(1, min(10, (int)$cfg->code_expiry_minutes));

    $code = str_pad((string)random_int(0, 999999), 6, '0', STR_PAD_LEFT);
    $challenge->code_hash = Hash::make($code);
    $challenge->expires_at = now()->addMinutes($ttl);
    $challenge->attempts = 0;
    $challenge->used = false;
    $challenge->save();

    $this->sendCode($challenge->user, $code);
    return $challenge;
  }

  protected function sendCode(User $user, string $code): void {
    $sms = SmsSetting::current();
    if (!$sms->is_active) {
      throw new \RuntimeException('SMS_SERVICE_DISABLED');
    }
    $client = SmsFactory::make($sms);
    $to = $user->phone_number ?? null;
    if (!$to) throw new \RuntimeException('USER_PHONE_NOT_VERIFIED');
    $client->send($to, "کد ورود شما: {$code} (اعتبار تا {$this->ttlText()})");
  }

  protected function ttlText(): string {
    $m = TwoFactorSetting::current()->code_expiry_minutes;
    return $m.' دقیقه';
  }
}
