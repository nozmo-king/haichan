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
            if (!Schema::hasColumn('personal_21e8_achievements', 'finder_id')) {
                Schema::table('personal_21e8_achievements', function (Blueprint $table) {
                    $table->foreignId('finder_id')->nullable()->constrained('bitcoin_auth')->onDelete('set null');
                });
            }
    
            if (!Schema::hasColumn('bitcoin_auth', 'runoff_pow_points')) {
                Schema::table('bitcoin_auth', function (Blueprint $table) {
                    $table->unsignedBigInteger('runoff_pow_points')->default(0);
                });
            }
    
            if (!Schema::hasColumn('posts', 'points')) {
                Schema::table('posts', function (Blueprint $table) {
                    $table->unsignedBigInteger('points')->default(0);
                });
            }
        }
    /**
     * Reverse the migrations.
     */
        public function down(): void
        {
            if (Schema::hasColumn('personal_21e8_achievements', 'finder_id')) {
                Schema::table('personal_21e8_achievements', function (Blueprint $table) {
                    $table->dropForeign(['finder_id']);
                    $table->dropColumn('finder_id');
                });
            }
    
            if (Schema::hasColumn('bitcoin_auth', 'runoff_pow_points')) {
                Schema::table('bitcoin_auth', function (Blueprint $table) {
                    $table->dropColumn('runoff_pow_points');
                });
            }
    
            if (Schema::hasColumn('posts', 'points')) {
                Schema::table('posts', function (Blueprint $table) {
                    $table->dropColumn('points');
                });
            }
        }};