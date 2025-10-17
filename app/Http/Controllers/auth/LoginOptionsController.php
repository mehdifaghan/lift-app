// app/Http/Controllers/Auth/LoginOptionsController.php
<?php
namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cache;
use Illuminate\Http\Request;

class LoginOptionsController extends Controller
{
    public function show(Request $request)
    {
        // کشِ 1 دقیقه‌ای برای سبک بودن
        $payload = Cache::remember('login_options_v1', 60, function () {
            // اینها از DB یا config بیاد
            $smsEnabled     = (bool) setting('login_sms_enabled', false);    // جدول settings
            $captchaEnabled = (bool) setting('captcha_enabled', true);
            $captchaType    = setting('captcha_provider', 'hcaptcha');       // 'hcaptcha' | 'recaptcha'
            $siteKey        = setting('captcha_site_key');                   // فقط SITE key، نه secret!
            $ttlSeconds     = (int) setting('login_sms_ttl', 300);           // 5 دقیقه
            $resendEvery    = (int) setting('login_sms_resend_seconds', 60); // هر 60 ثانیه

            return [
                'sms_login_enabled' => $smsEnabled,
                'captcha' => [
                    'enabled'  => $captchaEnabled,
                    'provider' => $captchaType,
                    'site_key' => $siteKey,
                ],
                'sms' => [
                    'ttl_seconds'        => $ttlSeconds,
                    'resend_every'       => $resendEvery,
                    'max_resend_attempts'=> (int) setting('login_sms_max_resend', 3),
                ],
                // گزینه‌های UX تکمیلی (دلخواه):
                'password_policy' => [
                    'min_length' => (int) setting('password_min_length', 8),
                ],
            ];
        });

        // هدرهای کش + CORS برای فرانت‌اند
        return response()->json($payload)
            ->header('Cache-Control', 'public, max-age=60, stale-while-revalidate=300')
            ->header('Access-Control-Allow-Origin', 'https://lift-app.com') // یا دامنه‌هات
            ->header('Vary', 'Origin');
    }
}
