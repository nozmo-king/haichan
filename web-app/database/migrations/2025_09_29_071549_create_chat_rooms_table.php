<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chat_rooms', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('slug', 100)->unique();
            $table->text('description')->nullable();
            $table->string('pow_difficulty', 10)->default('21e8'); // PoW pattern requirement
            $table->integer('min_pow_points')->default(1); // Minimum PoW points to enter
            $table->boolean('is_active')->default(true);
            $table->boolean('is_public')->default(true);
            $table->integer('max_users')->default(100);
            $table->integer('message_rate_limit')->default(5); // messages per minute per user
            $table->json('moderators')->nullable(); // User IDs who can moderate
            $table->timestamps();
        });

        Schema::create('chat_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('chat_room_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('username', 100)->nullable(); // For display (can be anonymous)
            $table->text('message');
            $table->string('message_hash', 64); // SHA256 of message content
            
            // Proof of Work fields
            $table->string('pow_hash', 64); // The mined hash
            $table->integer('pow_nonce'); // The nonce used
            $table->string('pow_pattern', 20); // The pattern matched (21e8, 777, etc.)
            $table->integer('pow_points')->default(1); // Points earned from this PoW
            $table->string('pow_challenge_id', 64); // Challenge ID for verification
            
            // Message metadata
            $table->string('ip_hash', 64)->nullable(); // Hashed IP for moderation
            $table->boolean('is_system')->default(false); // System messages
            $table->boolean('is_deleted')->default(false);
            $table->timestamp('deleted_at')->nullable();
            $table->json('metadata')->nullable(); // Additional data (reactions, etc.)
            
            $table->timestamps();
            
            // Indexes for performance
            $table->index(['chat_room_id', 'created_at']);
            $table->index(['user_id', 'created_at']);
            $table->index(['pow_points', 'created_at']);
        });

        Schema::create('chat_room_users', function (Blueprint $table) {
            $table->id();
            $table->foreignId('chat_room_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('display_name', 100)->nullable();
            $table->timestamp('joined_at');
            $table->timestamp('last_seen_at')->nullable();
            $table->integer('total_messages')->default(0);
            $table->integer('total_pow_points')->default(0);
            $table->boolean('is_muted')->default(false);
            $table->timestamp('muted_until')->nullable();
            $table->json('permissions')->nullable(); // Custom permissions
            
            $table->timestamps();
            $table->unique(['chat_room_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chat_room_users');
        Schema::dropIfExists('chat_messages');
        Schema::dropIfExists('chat_rooms');
    }
};