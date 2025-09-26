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
        // Drop the existing foreign key constraint and recreate it to reference bitcoin_auth
        Schema::table('threads', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
        });
        
        Schema::table('threads', function (Blueprint $table) {
            $table->foreign('user_id')->references('id')->on('bitcoin_auth')->onDelete('set null');
        });
        
        // Also fix posts table if it has the same issue
        try {
            Schema::table('posts', function (Blueprint $table) {
                $table->dropForeign(['user_id']);
            });
            
            Schema::table('posts', function (Blueprint $table) {
                $table->foreign('user_id')->references('id')->on('bitcoin_auth')->onDelete('set null');
            });
        } catch (\Exception $e) {
            // Posts table might not have this constraint issue
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('threads', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
        });
        
        Schema::table('threads', function (Blueprint $table) {
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
        });
        
        try {
            Schema::table('posts', function (Blueprint $table) {
                $table->dropForeign(['user_id']);
            });
            
            Schema::table('posts', function (Blueprint $table) {
                $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
            });
        } catch (\Exception $e) {
            // Posts table might not have this constraint issue
        }
    }
};