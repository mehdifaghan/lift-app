<?php
// app/Models/SmsSetting.php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class SmsSetting extends Model {
  protected $table = 'sms_settings';
  protected $fillable = [
    'provider','auth_method','api_key','username','password','sender','is_active','last_tested_at','last_test_status'
  ];
  protected $casts = [
    'is_active'=>'boolean',
    'last_tested_at'=>'datetime',
  ];

  // رمزنگاری کلیدها (نیاز به APP_KEY)
  protected $hidden = ['api_key','password'];
  public function setApiKeyAttribute($v){ $this->attributes['api_key'] = $v ? encrypt($v) : null; }
  public function getApiKeyDecryptedAttribute(){ return $this->api_key ? decrypt($this->api_key) : null; }
  public function setPasswordAttribute($v){ $this->attributes['password'] = $v ? encrypt($v) : null; }
  public function getPasswordDecryptedAttribute(){ return $this->password ? decrypt($this->password) : null; }

  public static function current(): self {
    return static::query()->firstOrCreate([], []);
  }
}
