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
            $table->string('sha256_digest', 64)->nullable()->unique();
            $table->boolean('is_pinned')->default(false);
            $table->boolean('is_highlighted')->default(false);
            $table->timestamp('pinned_until')->nullable();
            $table->timestamp('highlighted_until')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('threads', function (Blueprint $table) {
            $table->dropColumn([
                'sha256_digest',
                'is_pinned',
                'is_highlighted', 
                'pinned_until',
                'highlighted_until'
            ]);
        });
    }
};