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
            $table->decimal('total_pow_points', 10, 2)->default(0)->after('posts_count');
            $table->integer('pow_submissions_count')->default(0)->after('total_pow_points');
            $table->timestamp('last_pow_at')->nullable()->after('pow_submissions_count');

            $table->index('total_pow_points');
            $table->index('last_pow_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('boards', function (Blueprint $table) {
            $table->dropIndex(['total_pow_points']);
            $table->dropIndex(['last_pow_at']);
            $table->dropColumn(['total_pow_points', 'pow_submissions_count', 'last_pow_at']);
        });
    }
};
