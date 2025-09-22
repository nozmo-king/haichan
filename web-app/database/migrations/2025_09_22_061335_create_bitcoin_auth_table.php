<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('bitcoin_auth', function (Blueprint $table) {
            $table->id();
            $table->string('public_key', 130)->unique();
            $table->string('address', 64)->unique();
            $table->string('username', 50)->unique();
            $table->decimal('mining_power', 8, 2)->default(1.0);
            $table->bigInteger('total_pow_points')->default(0);
            $table->string('invite_code', 12)->unique();
            $table->string('invited_by', 12)->nullable();
            $table->timestamp('last_login')->nullable();
            $table->integer('mining_streak')->default(0);
            $table->integer('level')->default(1);
            $table->boolean('is_admin')->default(false);
            $table->boolean('is_moderator')->default(false);
            $table->boolean('is_banned')->default(false);
            $table->timestamp('banned_until')->nullable();
            $table->text('ban_reason')->nullable();
            $table->string('remember_token')->nullable();
            $table->timestamps();

            $table->index(['total_pow_points', 'level']);
            $table->index(['mining_streak']);
            $table->index(['last_login']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bitcoin_auth');
    }
};
