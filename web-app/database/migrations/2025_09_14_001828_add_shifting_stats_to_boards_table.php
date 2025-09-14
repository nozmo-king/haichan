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
        Schema::table('boards', function (Blueprint $table) {
            $table->unsignedBigInteger('total_pow')->default(0);
            $table->unsignedBigInteger('daily_pow')->default(0);
            $table->unsignedBigInteger('weekly_pow')->default(0);
            $table->decimal('activity_score', 10, 2)->default(0);
            $table->integer('display_order')->default(0);
            $table->json('shift_metadata')->nullable();
            $table->timestamp('last_pow_update')->nullable();

            $table->index(['activity_score', 'total_pow']);
            $table->index(['display_order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('boards', function (Blueprint $table) {
            $table->dropColumn([
                'total_pow',
                'daily_pow',
                'weekly_pow',
                'activity_score',
                'display_order',
                'shift_metadata',
                'last_pow_update'
            ]);
        });
    }
};
