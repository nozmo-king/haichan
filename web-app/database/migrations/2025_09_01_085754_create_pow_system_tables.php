<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // friend_codes already exists, skip it

        // Create user_sessions if it doesn't exist
        if (! Schema::hasTable('user_sessions')) {
            Schema::create('user_sessions', function (Blueprint $table) {
                $table->string('user_token', 16)->primary();
                $table->string('friend_code_used', 64);
                $table->string('username', 32)->nullable();
                $table->timestamp('last_seen');
                $table->unsignedBigInteger('total_pow_score')->default(0);
                $table->timestamps();
            });
        }

        // Create pow_submissions
        if (! Schema::hasTable('pow_submissions')) {
            Schema::create('pow_submissions', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('thread_id');
                $table->string('user_token', 16);
                $table->string('nonce', 64);
                $table->string('hash', 64);
                $table->string('difficulty_prefix', 8);
                $table->integer('mining_duration_ms');
                $table->timestamps();

                $table->index(['thread_id', 'created_at']);
                $table->index(['user_token', 'created_at']);
            });
        }

        // Add PoW columns to threads if they don't exist
        if (Schema::hasTable('threads')) {
            Schema::table('threads', function (Blueprint $table) {
                if (! Schema::hasColumn('threads', 'total_pow_score')) {
                    $table->unsignedBigInteger('total_pow_score')->default(0);
                }
                if (! Schema::hasColumn('threads', 'last_bump')) {
                    $table->timestamp('last_bump')->nullable();
                }
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('pow_submissions');
        Schema::dropIfExists('user_sessions');

        if (Schema::hasTable('threads')) {
            Schema::table('threads', function (Blueprint $table) {
                if (Schema::hasColumn('threads', 'total_pow_score')) {
                    $table->dropColumn('total_pow_score');
                }
                if (Schema::hasColumn('threads', 'last_bump')) {
                    $table->dropColumn('last_bump');
                }
            });
        }
    }
};
