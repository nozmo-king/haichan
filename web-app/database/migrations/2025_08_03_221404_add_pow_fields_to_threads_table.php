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
        Schema::table('threads', function (Blueprint $table) {
            $table->bigInteger('pow_nonce')->nullable();
            $table->string('pow_hash', 64)->nullable();
            $table->bigInteger('pow_timestamp')->nullable();
            $table->index('pow_hash');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('threads', function (Blueprint $table) {
            $table->dropColumn(['pow_nonce', 'pow_hash', 'pow_timestamp']);
        });
    }
};
