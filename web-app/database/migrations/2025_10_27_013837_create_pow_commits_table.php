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
        Schema::create('pow_v1_commits', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('challenge_id')->index();
            $table->unsignedBigInteger('nonce_u64');
            $table->integer('miner_version');
            $table->bigInteger('timestamp_i64');
            $table->string('solved_hash_hex', 64);
            $table->boolean('accepted')->index();
            $table->text('reject_reason')->nullable();
            $table->integer('solve_time_ms')->nullable();
            $table->timestamps();
            
            $table->foreign('challenge_id')->references('id')->on('pow_v1_challenges')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pow_v1_commits');
    }
};
