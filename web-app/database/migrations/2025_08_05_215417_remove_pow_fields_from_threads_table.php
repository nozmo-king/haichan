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
        Schema::table('threads', function (Blueprint $table) {
            $table->dropIndex(['pow_hash']);
            $table->dropIndex(['pow_challenge_id']);
            $table->dropColumn(['pow_nonce', 'pow_hash', 'pow_challenge_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('threads', function (Blueprint $table) {
            $table->bigInteger('pow_nonce')->nullable();
            $table->string('pow_hash', 64)->nullable();
            $table->string('pow_challenge_id', 32)->nullable();
            $table->index('pow_hash');
            $table->index('pow_challenge_id');
        });
    }
};
