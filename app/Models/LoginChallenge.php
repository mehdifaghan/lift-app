<?php
// app/Models/LoginChallenge.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class LoginChallenge extends Model {
  use HasFactory, HasUuids;
  protected $table = 'login_challenges';
  protected $fillable = [
    'user_id','code_hash','expires_at','attempts','max_attempts','ip','user_agent','used'
  ];
  protected $casts = [
    'expires_at' => 'datetime',
    'used' => 'boolean',
  ];
  public function user(){ return $this->belongsTo(User::class); }
}
