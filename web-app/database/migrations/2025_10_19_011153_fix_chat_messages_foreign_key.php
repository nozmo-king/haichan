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
        // Drop the foreign key constraint and recreate with correct reference
        Schema::table('chat_messages', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
        });
        
        Schema::table('chat_messages', function (Blueprint $table) {
            $table->foreign('user_id')->references('id')->on('bitcoin_auth')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('chat_messages', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
        });
        
        Schema::table('chat_messages', function (Blueprint $table) {
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
        });
    }
};