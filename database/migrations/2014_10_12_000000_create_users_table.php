<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  /**
   * Run the migrations.
   */
  public function up(): void
  {
    Schema::create('users', function (Blueprint $table) {
      $table->id();
      $table->string('username', 100)->unique();
      $table->string('name', 100);
      $table->string('email', 150)->nullable()->unique();
      $table->timestamp('email_verified_at')->nullable();
      $table->dateTime('last_login')->nullable();
      $table->string('password');
      $table->string('token')->nullable();
      $table->unsignedBigInteger('id_siswa')->nullable();
      $table->string('login_token')->nullable();
      $table->boolean('is_active')->default(1);
      $table->rememberToken();
      $table->timestamps();
      $table->softDeletes();
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('users');
  }
};
