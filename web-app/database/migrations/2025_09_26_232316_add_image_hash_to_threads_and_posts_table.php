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
            $table->string('image_hash', 64)->nullable()->index();
        });

        Schema::table('posts', function (Blueprint $table) {
            $table->string('image_hash', 64)->nullable()->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('threads', function (Blueprint $table) {
            $table->dropColumn('image_hash');
        });

        Schema::table('posts', function (Blueprint $table) {
            $table->dropColumn('image_hash');
        });
    }
};
