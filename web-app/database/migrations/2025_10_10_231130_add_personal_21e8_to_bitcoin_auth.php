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
            $table->string('personal_21e8_hash', 64)->nullable()->after('total_pow_points');
            $table->integer('personal_21e8_nonce')->nullable()->after('personal_21e8_hash');
            $table->integer('personal_21e8_total_hashes')->default(0)->after('personal_21e8_nonce');
            $table->float('personal_21e8_mining_time')->nullable()->after('personal_21e8_total_hashes');
            $table->timestamp('personal_21e8_found_at')->nullable()->after('personal_21e8_mining_time');
            
            $table->index('personal_21e8_hash');
            $table->index('personal_21e8_found_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bitcoin_auth', function (Blueprint $table) {
            $table->dropColumn([
                'personal_21e8_hash',
                'personal_21e8_nonce',
                'personal_21e8_total_hashes',
                'personal_21e8_mining_time',
                'personal_21e8_found_at'
            ]);
        });
    }
};
