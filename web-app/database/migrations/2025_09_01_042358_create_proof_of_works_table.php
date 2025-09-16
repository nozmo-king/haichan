<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('proof_of_works', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('thread_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('hash', 64)->unique();
            $table->bigInteger('nonce');
            $table->text('data');
            $table->string('pattern', 20);
            $table->integer('points')->default(0);
            $table->timestamp('verified_at')->nullable();
            $table->ipAddress('ip_address');
            $table->timestamps();

            $table->index(['pattern', 'created_at']);
            $table->index(['user_id', 'points']);
            $table->index(['thread_id', 'points']);
            $table->index('verified_at');
        });
    }

    public function down()
    {
        Schema::dropIfExists('proof_of_works');
    }
};
