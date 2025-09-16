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
        Schema::create('proof_submissions', function (Blueprint $table) {
            $table->id();
            $table->string('user_session', 64)->index(); // Anonymous session ID based on IP+time
            $table->string('target_type', 32); // 'global', 'board', 'thread', 'post', 'image'
            $table->string('target_id', 64); // board code, thread id, etc.
            $table->string('pattern', 16); // '21e8', '21e80', etc.
            $table->string('hash', 64); // The proof hash
            $table->bigInteger('nonce');
            $table->text('challenge_data'); // The data that was hashed
            $table->string('content_hash', 64)->nullable(); // SHA256 of the content being mined
            $table->decimal('difficulty', 8, 2)->default(1.0);
            $table->string('ip_address', 45)->nullable();
            $table->json('metadata')->nullable(); // Additional data like hash rate, etc.
            $table->timestamps();

            // Indexes for efficient querying
            $table->index(['user_session', 'created_at']);
            $table->index(['target_type', 'target_id']);
            $table->index(['pattern', 'created_at']);
            $table->index('content_hash');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('proof_submissions');
    }
};
