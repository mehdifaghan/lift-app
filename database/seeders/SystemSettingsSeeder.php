<?php
// database/seeders/SystemSettingsSeeder.php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
use App\Models\{SmsSetting, TwoFactorSetting};

class SystemSettingsSeeder extends Seeder {
  public function run(): void {
    SmsSetting::firstOrCreate([], [
      'provider'=>'Kavenegar','auth_method'=>'apikey','is_active'=>false
    ]);
    TwoFactorSetting::firstOrCreate([], [
      'is_enabled'=>false,'code_expiry_minutes'=>5,'max_attempts'=>3,'require_for_all_roles'=>true,'exempted_roles'=>[]
    ]);
  }
}
