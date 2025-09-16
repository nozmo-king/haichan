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
        Schema::table('proof_submissions', function (Blueprint $table) {
            $table->bigInteger('hashes_computed')->default(0)->after('difficulty');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('proof_submissions', function (Blueprint $table) {
            $table->dropColumn('hashes_computed');
        });
    }
};
