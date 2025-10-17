<?php
// app/Http/Controllers/Api/v1/SystemAdmin/Settings/TwoFactorController.php
namespace App\Http\Controllers\Api\v1\SystemAdmin\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\SystemAdmin\TwoFactor\UpdateTwoFactorRequest;
use App\Models\{TwoFactorSetting,SmsSetting};

class TwoFactorController extends Controller {
  public function show() {
    $cfg = TwoFactorSetting::current();
    return response()->json([
      'success'=>true,
      'data'=>[
        'isEnabled'=>$cfg->is_enabled,
        'codeExpiryMinutes'=>$cfg->code_expiry_minutes,
        'maxAttempts'=>$cfg->max_attempts,
        'requireForAllRoles'=>$cfg->require_for_all_roles,
        'exemptedRoles'=>$cfg->exempted_roles ?? [],
        'createdAt'=>$cfg->created_at?->toIso8601String(),
        'updatedAt'=>$cfg->updated_at?->toIso8601String(),
      ]
    ]);
  }

  public function update(UpdateTwoFactorRequest $req) {
    $sms = SmsSetting::current();
    if ($req->boolean('isEnabled') && !$sms->is_active) {
      return response()->json([
        'success'=>false,
        'error'=>['code'=>'SMS_SERVICE_DISABLED','message'=>'ابتدا سرویس پیامک را فعال کنید']
      ], 400);
    }

    $cfg = TwoFactorSetting::current();
    $cfg->fill([
      'is_enabled' => $req->boolean('isEnabled'),
      'code_expiry_minutes' => $req->integer('codeExpiryMinutes'),
      'max_attempts' => $req->integer('maxAttempts'),
      'require_for_all_roles' => $req->boolean('requireForAllRoles'),
      'exempted_roles' => $req->input('exemptedRoles', []),
    ])->save();

    return response()->json([
      'success'=>true,
      'message'=>'تنظیمات احراز هویت دو مرحله‌ای با موفقیت بروزرسانی شد',
      'data'=>[
        'isEnabled'=>$cfg->is_enabled,
        'codeExpiryMinutes'=>$cfg->code_expiry_minutes,
        'maxAttempts'=>$cfg->max_attempts,
        'requireForAllRoles'=>$cfg->require_for_all_roles,
        'exemptedRoles'=>$cfg->exempted_roles ?? [],
        'updatedAt'=>$cfg->updated_at?->toIso8601String(),
      ]
    ]);
  }
}
