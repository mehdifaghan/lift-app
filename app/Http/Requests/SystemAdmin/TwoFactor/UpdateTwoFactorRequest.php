<?php
// app/Http/Requests/SystemAdmin/TwoFactor/UpdateTwoFactorRequest.php
namespace App\Http\Requests\SystemAdmin\TwoFactor;
use Illuminate\Foundation\Http\FormRequest;

class UpdateTwoFactorRequest extends FormRequest {
  public function authorize(): bool { return true; }
  public function rules(): array {
    return [
      'isEnabled' => ['required','boolean'],
      'codeExpiryMinutes' => ['required','integer','min:1','max:10'],
      'maxAttempts' => ['required','integer','min:3','max:5'],
      'requireForAllRoles' => ['required','boolean'],
      'exemptedRoles' => ['nullable','array'],
      'exemptedRoles.*' => ['string','max:64'],
    ];
  }
}
