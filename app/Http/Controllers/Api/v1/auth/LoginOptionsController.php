<?php
// app/Http/Controllers/Api/v1/Auth/LoginOptionsController.php
namespace App\Http\Controllers\Api\v1\Auth;

use App\Http\Controllers\Controller;
use App\Models\{TwoFactorSetting, SmsSetting};
use Illuminate\Support\Facades\Cache;

class LoginOptionsController extends Controller {
  public function show() {
    $payload = Cache::remember('login_options_v1', 60, function () {
      $two = TwoFactorSetting::current();
      $sms = SmsSetting::current();
      return [
        'sms_login_enabled' => (bool)$two->is_enabled,
        'captcha' => [
          'enabled' => (bool)(config('captcha.enabled', true)),
          'provider'=> config('captcha.provider', 'hcaptcha'),
          'site_key'=> config('captcha.site_key'),
        ],
        'sms' => [
          'ttl_seconds' => (int)$two->code_expiry_minutes * 60,
          'resend_every'=> 60,
          'max_resend_attempts'=> 3,
          'service_active' => (bool)$sms->is_active,
        ],
      ];
    });

    return response()->json($payload)
      ->header('Cache-Control', 'public, max-age=60, stale-while-revalidate=300');
  }
}
