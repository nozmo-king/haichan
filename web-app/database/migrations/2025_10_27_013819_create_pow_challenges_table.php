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
        Schema::create('pow_v1_challenges', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('user_pubkey_hex', 66)->index();
            $table->enum('scope', ['thread', 'reply']);
            $table->unsignedBigInteger('thread_id')->nullable();
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->binary('post_bytes_hash');
            $table->string('required_prefix_hex', 16);
            $table->integer('challenge_version')->default(1);
            $table->timestamp('expires_at')->index();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pow_v1_challenges');
    }
};
