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
        Schema::table('proof_of_works', function (Blueprint $table) {
            $table->foreignId('post_id')->nullable()->constrained()->onDelete('cascade');
            $table->index('post_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('proof_of_works', function (Blueprint $table) {
            $table->dropForeign(['post_id']);
            $table->dropIndex(['post_id']);
            $table->dropColumn('post_id');
        });
    }
};
