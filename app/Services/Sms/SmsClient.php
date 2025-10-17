<?php
// app/Services/Sms/SmsClient.php
namespace App\Services\Sms;

interface SmsClient {
  public function send(string $to, string $message): string; // returns messageId
  public static function makeFromSettings(\App\Models\SnsSetting|\App\Models\SmsSetting $settings): self;
}
