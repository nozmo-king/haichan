<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('proof_of_works', function (Blueprint $table) {
            $table->unsignedBigInteger('challenge_id')->nullable()->after('user_id');
            
            $table->foreign('challenge_id')
                ->references('id')
                ->on('pow_challenges')
                ->onDelete('set null');
            
            $table->index('challenge_id');
        });
    }

    public function down(): void
    {
        Schema::table('proof_of_works', function (Blueprint $table) {
            $table->dropForeign(['challenge_id']);
            $table->dropColumn('challenge_id');
        });
    }
};
