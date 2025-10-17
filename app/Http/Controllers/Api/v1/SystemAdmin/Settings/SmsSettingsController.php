<?php
// app/Http/Controllers/Api/v1/SystemAdmin/Settings/SmsSettingsController.php
namespace App\Http\Controllers\Api\v1\SystemAdmin\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\SystemAdmin\Sms\{UpdateSmsSettingsRequest, TestSmsRequest};
use App\Models\SmsSetting;
use App\Services\Sms\SmsFactory;
use Illuminate\Support\Str;

class SmsSettingsController extends Controller {
  public function show() {
    $s = SmsSetting::current();
    return response()->json([
      'success'=>true,
      'data'=>[
        'provider'=>$s->provider,
        'authMethod'=>$s->auth_method,
        'apiKey'=> $s->api_key ? '••••••••••••••••' : null,
        'username'=>$s->username,
        'password'=> $s->password ? '••••••••' : null,
        'sender'=>$s->sender,
        'isActive'=>$s->is_active,
        'lastTestedAt'=>$s->last_tested_at?->toIso8601String(),
        'lastTestStatus'=>$s->last_test_status,
        'createdAt'=>$s->created_at?->toIso8601String(),
        'updatedAt'=>$s->updated_at?->toIso8601String(),
      ]
    ]);
  }

  public function update(UpdateSmsSettingsRequest $req) {
    $s = SmsSetting::current();
    $s->provider = $req->string('provider');
    $s->auth_method = $req->string('authMethod');
    if ($req->filled('apiKey')) $s->api_key = $req->string('apiKey');
    if ($req->filled('username')) $s->username = $req->string('username');
    if ($req->filled('password')) $s->password = $req->string('password');
    $s->sender = $req->string('sender');
    $s->is_active = $req->boolean('isActive');
    $s->save();

    return response()->json([
      'success'=>true,
      'message'=>'تنظیمات پیامک با موفقیت بروزرسانی شد',
      'data'=>[
        'provider'=>$s->provider,
        'authMethod'=>$s->auth_method,
        'sender'=>$s->sender,
        'isActive'=>$s->is_active,
        'updatedAt'=>$s->updated_at?->toIso8601String(),
      ]
    ]);
  }

  public function test(TestSmsRequest $req) {
    $s = SmsSetting::current();
    if (!$s->is_active) {
      return response()->json([
        'success'=>false,
        'error'=>['code'=>'SMS_SERVICE_ERROR','message'=>'سرویس پیامک غیرفعال است']
      ], 503);
    }
    try {
      $client = SmsFactory::make($s);
      $msgId = $client->send($req->string('phoneNumber'), 'پیامک تست Lift App');
      $s->last_test_status = 'success';
      $s->last_tested_at = now();
      $s->save();

      return response()->json([
        'success'=>true,
        'message'=>'پیامک تست با موفقیت ارسال شد',
        'data'=>[
          'messageId'=>$msgId,
          'status'=>'sent',
          'recipient'=>$req->string('phoneNumber'),
          'sentAt'=>now()->toIso8601String()
        ]
      ]);
    } catch (\Throwable $e) {
      $s->last_test_status = 'failed';
      $s->last_tested_at = now();
      $s->save();

      return response()->json([
        'success'=>false,
        'error'=>[
          'code'=>'SMS_SERVICE_ERROR',
          'message'=>'خطا در ارسال پیامک. لطفاً تنظیمات را بررسی کنید',
          'details'=>[
            'provider'=>$s->provider,
            'errorCode'=> method_exists($e,'getCode') ? (string)$e->getCode() : null,
            'errorMessage'=> $e->getMessage()
          ]
        ]
      ], 503);
    }
  }
}
