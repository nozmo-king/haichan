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
        Schema::table('posts', function (Blueprint $table) {
            $table->bigInteger('pow_nonce')->nullable();
            $table->string('pow_hash', 64)->nullable();
            $table->string('pow_challenge_id', 32)->nullable();
            $table->string('pow_pattern', 16)->default('21e8');
            $table->decimal('pow_difficulty', 8, 2)->default(1.0);
            $table->timestamp('pow_verified_at')->nullable();
            $table->index('pow_hash');
            $table->index('pow_challenge_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->dropIndex(['pow_hash']);
            $table->dropIndex(['pow_challenge_id']);
            $table->dropColumn(['pow_nonce', 'pow_hash', 'pow_challenge_id', 'pow_pattern', 'pow_difficulty', 'pow_verified_at']);
        });
    }
};
