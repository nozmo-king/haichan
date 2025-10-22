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
        // Add pubkey_hex to existing users table if it doesn't exist
        if (!Schema::hasColumn('users', 'pubkey_hex')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('pubkey_hex', 66)->unique()->nullable();
            });
        }

        // Create posts table for PoW system (separate from existing posts if any)
        Schema::create('pow_posts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('thread_id')->nullable();
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->string('author_pubkey_hex', 66);
            $table->string('title');
            $table->text('body');
            $table->json('attachments_json')->default('[]');
            $table->timestamps();
            
            $table->index(['thread_id']);
            $table->index(['parent_id']);
            $table->index(['author_pubkey_hex']);
        });

        Schema::create('pow_challenges', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('user_pubkey_hex', 66);
            $table->enum('scope', ['thread', 'reply']);
            $table->unsignedBigInteger('thread_id')->nullable();
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->binary('post_bytes_hash'); // 32 bytes
            $table->string('required_prefix_hex');
            $table->integer('challenge_version');
            $table->timestamp('expires_at');
            $table->timestamps();
            
            $table->index(['user_pubkey_hex', 'scope']);
            $table->index(['expires_at']);
        });

        Schema::create('pow_commits', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('challenge_id');
            $table->unsignedBigInteger('nonce_u64');
            $table->integer('miner_version');
            $table->bigInteger('timestamp_i64');
            $table->string('solved_hash_hex', 64);
            $table->boolean('accepted');
            $table->text('reject_reason')->nullable();
            $table->integer('solve_time_ms')->nullable();
            $table->timestamps();
            
            $table->foreign('challenge_id')->references('id')->on('pow_challenges');
            $table->index(['challenge_id']);
            $table->index(['accepted']);
        });

        Schema::create('op_receipts', function (Blueprint $table) {
            $table->uuid('client_op_id')->primary();
            $table->text('result_json');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('op_receipts');
        Schema::dropIfExists('pow_commits');
        Schema::dropIfExists('pow_challenges');
        Schema::dropIfExists('pow_posts');
        
        // Only drop pubkey_hex column if we added it
        if (Schema::hasColumn('users', 'pubkey_hex')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('pubkey_hex');
            });
        }
    }
};
