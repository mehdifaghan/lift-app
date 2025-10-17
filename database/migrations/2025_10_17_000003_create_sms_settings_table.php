<?php
// database/migrations/2025_10_17_000003_create_sms_settings_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void {
    Schema::create('sms_settings', function (Blueprint $t) {
      $t->id();
      $t->enum('provider', ['Kavenegar','Melipayamak','Farazsms','Magfa','Ghasedak'])->default('Kavenegar');
      $t->enum('auth_method', ['apikey','userpass'])->default('apikey');
      $t->string('api_key')->nullable(); // encrypted در مدل
      $t->string('username')->nullable();
      $t->string('password')->nullable(); // encrypted در مدل
      $t->string('sender', 20)->nullable();
      $t->boolean('is_active')->default(false);
      $t->timestamp('last_tested_at')->nullable();
      $t->enum('last_test_status', ['success','failed'])->nullable();
      $t->timestamps();
    });
  }
  public function down(): void {
    Schema::dropIfExists('sms_settings');
  }
};
