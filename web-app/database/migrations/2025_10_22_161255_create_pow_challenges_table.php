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
        Schema::create('pow_challenges', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('user_pubkey_hex');
            $table->enum('scope', ['thread', 'reply']);
            $table->unsignedBigInteger('thread_id')->nullable();
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->binary('post_bytes_hash', 32);
            $table->string('required_prefix_hex');
            $table->integer('challenge_version');
            $table->timestamp('expires_at');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pow_challenges');
    }
};
