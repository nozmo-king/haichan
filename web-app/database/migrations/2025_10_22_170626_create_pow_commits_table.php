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
        Schema::create('pow_commits', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('challenge_id');
            $table->unsignedBigInteger('nonce_u64');
            $table->integer('miner_version');
            $table->bigInteger('timestamp_i64');
            $table->char('solved_hash_hex', 64);
            $table->boolean('accepted');
            $table->text('reject_reason')->nullable();
            $table->integer('solve_time_ms');
            $table->timestamps();

            $table->foreign('challenge_id')->references('id')->on('pow_challenges');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pow_commits');
    }
};