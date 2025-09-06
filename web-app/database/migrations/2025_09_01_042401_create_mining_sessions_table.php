<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('mining_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade');
            $table->ipAddress('ip_address');
            $table->string('user_agent')->nullable();
            $table->integer('hashes_computed')->default(0);
            $table->integer('valid_proofs')->default(0);
            $table->integer('points_earned')->default(0);
            $table->timestamp('started_at');
            $table->timestamp('last_activity')->nullable();
            $table->timestamp('ended_at')->nullable();
            $table->boolean('active')->default(true);
            $table->timestamps();

            $table->index(['user_id', 'active']);
            $table->index(['ip_address', 'started_at']);
            $table->index('last_activity');
        });
    }

    public function down()
    {
        Schema::dropIfExists('mining_sessions');
    }
};
