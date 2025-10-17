<?php

namespace App\Services\Sms;

use App\Models\SmsSetting;

interface SmsClient
{
    public function send(string $to, string $message): string;

    public static function makeFromSettings(SmsSetting $settings): self;
}
