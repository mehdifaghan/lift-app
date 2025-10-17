<?php
// app/Services/Sms/SmsFactory.php
namespace App\Services\Sms;

use App\Models\SmsSetting;
use App\Services\Sms\Providers\KavenegarClient;

class SmsFactory {
  public static function make(SmsSetting $settings): SmsClient {
    return match($settings->provider) {
      'Kavenegar' => KavenegarClient::makeFromSettings($settings),
      default => KavenegarClient::makeFromSettings($settings), // به دلخواه توسعه بده
    };
  }
}
