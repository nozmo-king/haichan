<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('personal_21e8_achievements', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('level'); // '21e8', '21e80', '21e800', '21e8000', etc.
            $table->string('hash', 64);
            $table->integer('nonce');
            $table->integer('total_hashes');
            $table->float('mining_time');
            $table->integer('points_awarded')->default(0);
            $table->timestamp('found_at');
            $table->timestamps();
            
            $table->foreign('user_id')->references('id')->on('bitcoin_auth')->onDelete('cascade');
            $table->unique(['user_id', 'level']);
            $table->index('level');
            $table->index('found_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('personal_21e8_achievements');
    }
};
