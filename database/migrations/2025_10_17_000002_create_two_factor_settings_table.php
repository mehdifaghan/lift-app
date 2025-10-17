<?php
// database/migrations/2025_10_17_000002_create_two_factor_settings_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void {
    Schema::create('two_factor_settings', function (Blueprint $t) {
      $t->id();
      $t->boolean('is_enabled')->default(false);
      $t->unsignedTinyInteger('code_expiry_minutes')->default(5); // 1..10
      $t->unsignedTinyInteger('max_attempts')->default(3);        // 3..5
      $t->boolean('require_for_all_roles')->default(true);
      $t->json('exempted_roles')->nullable(); // []
      $t->timestamps();
    });
  }
  public function down(): void {
    Schema::dropIfExists('two_factor_settings');
  }
};
