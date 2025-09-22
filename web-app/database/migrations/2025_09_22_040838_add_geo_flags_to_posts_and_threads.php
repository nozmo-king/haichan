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
        Schema::table('posts', function (Blueprint $table) {
            $table->string('ip_address')->nullable();
            $table->string('country_flag', 10)->nullable();
        });

        Schema::table('threads', function (Blueprint $table) {
            $table->string('ip_address')->nullable();
            $table->string('country_flag', 10)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->dropColumn(['ip_address', 'country_flag']);
        });

        Schema::table('threads', function (Blueprint $table) {
            $table->dropColumn(['ip_address', 'country_flag']);
        });
    }
};
