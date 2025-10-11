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
        Schema::create('user_attestations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('bitcoin_auth')->onDelete('cascade');
            $table->string('platform', 50); // x, reddit, github, pgp, btc, zec, eth, xrp, xlm
            $table->string('identifier'); // username, wallet address, key fingerprint, etc.
            $table->text('proof_url')->nullable(); // Link to proof (tweet, gist, etc.)
            $table->text('proof_content')->nullable(); // Stored proof content
            $table->string('verification_hash')->nullable(); // Hash of proof for verification
            $table->boolean('is_verified')->default(false);
            $table->timestamp('verified_at')->nullable();
            $table->timestamps();
            
            $table->unique(['user_id', 'platform', 'identifier']);
            $table->index(['user_id', 'is_verified']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_attestations');
    }
};