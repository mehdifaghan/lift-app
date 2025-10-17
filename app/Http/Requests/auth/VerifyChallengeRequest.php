<?php
// app/Http/Requests/Auth/VerifyChallengeRequest.php
namespace App\Http\Requests\Auth;
use Illuminate\Foundation\Http\FormRequest;

class VerifyChallengeRequest extends FormRequest {
  public function authorize(): bool { return true; }
  public function rules(): array {
    return [
      'challenge_id' => ['required','uuid','exists:login_challenges,id'],
      'code' => ['required','digits:6'],
    ];
  }
}
