<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pow_challenges', function (Blueprint $table) {
            $table->id();
            $table->uuid('token')->unique();
            $table->unsignedBigInteger('board_id')->nullable();
            $table->string('target_type', 50)->nullable();
            $table->string('target_id', 255)->nullable();
            $table->string('action', 50);
            $table->string('difficulty', 50);
            $table->string('server_nonce', 64);
            $table->json('canonical_payload');
            $table->string('hmac_signature', 64);
            $table->timestamp('issued_at');
            $table->timestamp('expires_at');
            $table->timestamp('used_at')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->timestamps();

            $table->index('token');
            $table->index('expires_at');
            $table->index('used_at');
            $table->index(['target_type', 'target_id']);
            
            $table->foreign('board_id')->references('id')->on('boards')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('bitcoin_auth')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pow_challenges');
    }
};
