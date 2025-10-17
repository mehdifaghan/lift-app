<?php
// app/Http/Requests/SystemAdmin/Sms/UpdateSmsSettingsRequest.php
namespace App\Http\Requests\SystemAdmin\Sms;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateSmsSettingsRequest extends FormRequest {
  public function authorize(): bool { return true; }
  public function rules(): array {
    return [
      'provider' => ['required', Rule::in(['Kavenegar','Melipayamak','Farazsms','Magfa','Ghasedak'])],
      'authMethod' => ['required', Rule::in(['apikey','userpass'])],
      'apiKey' => ['nullable','string','min:10', 'required_if:authMethod,apikey'],
      'username' => ['nullable','string','min:3', 'required_if:authMethod,userpass'],
      'password' => ['nullable','string','min:6', 'required_if:authMethod,userpass'],
      'sender' => ['required','string','regex:/^[0-9]{4,15}$/'],
      'isActive' => ['required','boolean'],
    ];
  }
}
