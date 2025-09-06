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
            $table->dropColumn('pow_timestamp');
            $table->string('pow_challenge_id', 32)->nullable()->after('pow_hash');
            $table->index('pow_challenge_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('threads', function (Blueprint $table) {
            $table->dropColumn('pow_challenge_id');
            $table->bigInteger('pow_timestamp')->nullable()->after('pow_hash');
        });
    }
};
