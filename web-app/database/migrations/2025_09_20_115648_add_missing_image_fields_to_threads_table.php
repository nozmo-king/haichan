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
            // Add missing image-related fields
            $table->string('image_original_name')->nullable()->after('image_filename');
            $table->integer('image_size')->nullable()->after('image_original_name');
            $table->integer('image_count')->default(0)->after('image_size');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('threads', function (Blueprint $table) {
            $table->dropColumn(['image_original_name', 'image_size', 'image_count']);
        });
    }
};