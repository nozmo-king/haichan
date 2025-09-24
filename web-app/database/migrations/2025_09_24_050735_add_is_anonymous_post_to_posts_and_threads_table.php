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
            $table->boolean('is_anonymous_post')->default(false);
        });

        Schema::table('threads', function (Blueprint $table) {
            $table->boolean('is_anonymous_post')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->dropColumn('is_anonymous_post');
        });

        Schema::table('threads', function (Blueprint $table) {
            $table->dropColumn('is_anonymous_post');
        });
    }
};
