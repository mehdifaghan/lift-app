<?php
// app/Models/TwoFactorSetting.php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class TwoFactorSetting extends Model {
  protected $table = 'two_factor_settings';
  protected $fillable = [
    'is_enabled','code_expiry_minutes','max_attempts','require_for_all_roles','exempted_roles'
  ];
  protected $casts = [
    'is_enabled'=>'boolean',
    'require_for_all_roles'=>'boolean',
    'exempted_roles'=>'array',
  ];
  public static function current(): self {
    return static::query()->firstOrCreate([], [
      'is_enabled'=>false,'code_expiry_minutes'=>5,'max_attempts'=>3,'require_for_all_roles'=>true,'exempted_roles'=>[]
    ]);
  }
}
