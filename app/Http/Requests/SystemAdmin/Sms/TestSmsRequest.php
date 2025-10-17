<?php
// app/Http/Requests/SystemAdmin/Sms/TestSmsRequest.php
namespace App\Http\Requests\SystemAdmin\Sms;
use Illuminate\Foundation\Http\FormRequest;

class TestSmsRequest extends FormRequest {
  public function authorize(): bool { return true; }
  public function rules(): array {
    return [
      'phoneNumber' => ['required','regex:/^\+?[1-9]\d{7,14}$/'],
    ];
  }
}
