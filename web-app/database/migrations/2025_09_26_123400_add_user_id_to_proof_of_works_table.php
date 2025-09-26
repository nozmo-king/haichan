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
        Schema::table('proof_of_works', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->nullable()->after('thread_id');
            $table->foreign('user_id')->references('id')->on('bitcoin_auth')->onDelete('set null');
            $table->index(['user_id', 'points']); // Index for user leaderboard queries
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('proof_of_works', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropIndex(['user_id', 'points']);
            $table->dropColumn('user_id');
        });
    }
};
