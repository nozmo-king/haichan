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
        Schema::table('image_library', function (Blueprint $table) {
            $table->integer('width')->nullable()->change();
            $table->integer('height')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('image_library', function (Blueprint $table) {
            $table->integer('width')->nullable(false)->change();
            $table->integer('height')->nullable(false)->change();
        });
    }
};
