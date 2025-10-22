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
        Schema::table('bitcoin_auth', function (Blueprint $table) {
            $table->timestamp('custom_tripcode_until')->nullable();
            $table->timestamp('pin_boost_until')->nullable();
            $table->timestamp('colored_name_until')->nullable();
            $table->timestamp('extra_images_until')->nullable();
            $table->timestamp('mining_boost_until')->nullable();
            $table->timestamp('thread_highlight_until')->nullable();
            $table->integer('priority_post_count')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bitcoin_auth', function (Blueprint $table) {
            $table->dropColumn([
                'custom_tripcode_until',
                'pin_boost_until', 
                'colored_name_until',
                'extra_images_until',
                'mining_boost_until',
                'thread_highlight_until',
                'priority_post_count'
            ]);
        });
    }
};