<?php
// app/Services/Sms/Providers/KavenegarClient.php
namespace App\Services\Sms\Providers;

use App\Models\SmsSetting;
use App\Services\Sms\SmsClient;
use Illuminate\Support\Facades\Http;

class KavenegarClient implements SmsClient {
  public function __construct(protected string $apiKey, protected string $sender){}
  public static function makeFromSettings(SmsSetting $settings): self {
    return new self($settings->api_key_decrypted ?? '', $settings->sender ?? '');
  }
  public function send(string $to, string $message): string {
    // توجه: URL واقعی و پارامترها را با مستندات رسمی چک کنید
    $res = Http::asForm()->post('https://api.kavenegar.com/v1/'.$this->apiKey.'/sms/send.json', [
      'sender' => $this->sender,
      'receptor' => $to,
      'message' => $message,
    ]);
    if(!$res->ok()) throw new \RuntimeException('SMS_SERVICE_ERROR');
    $json = $res->json();
    return (string) data_get($json, 'entries.0.messageid', uniqid('msg_', true));
  }
}
