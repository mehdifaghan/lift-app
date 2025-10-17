<?php
// app/Http/Requests/Auth/ResendChallengeRequest.php
namespace App\Http\Requests\Auth;
use Illuminate\Foundation\Http\FormRequest;

class ResendChallengeRequest extends FormRequest {
  public function authorize(): bool { return true; }
  public function rules(): array {
    return [
      'challenge_id' => ['required','uuid','exists:login_challenges,id'],
    ];
  }
}
