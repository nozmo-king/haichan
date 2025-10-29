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
            $table->string('password_hash')->nullable()->after('signature');
            $table->string('password_salt')->nullable()->after('password_hash');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bitcoin_auth', function (Blueprint $table) {
            $table->dropColumn(['password_hash', 'password_salt']);
        });
    }
};
