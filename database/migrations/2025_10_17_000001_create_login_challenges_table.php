<?php
// database/migrations/2025_10_17_000001_create_login_challenges_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void {
    Schema::create('login_challenges', function (Blueprint $t) {
      $t->uuid('id')->primary();
      $t->foreignId('user_id')->constrained()->cascadeOnDelete();
      $t->string('code_hash', 255);
      $t->timestamp('expires_at');
      $t->unsignedTinyInteger('attempts')->default(0);
      $t->unsignedTinyInteger('max_attempts')->default(5);
      $t->string('ip', 45)->nullable();
      $t->string('user_agent', 255)->nullable();
      $t->boolean('used')->default(false);
      $t->timestamps();

      $t->index(['user_id', 'expires_at', 'used']);
    });
  }
  public function down(): void {
    Schema::dropIfExists('login_challenges');
  }
};
