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
        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('from_user_id');
            $table->unsignedBigInteger('to_user_id');
            $table->string('subject', 255)->nullable();
            $table->text('content');
            $table->boolean('is_read')->default(false);
            $table->boolean('from_deleted')->default(false);
            $table->boolean('to_deleted')->default(false);
            $table->timestamp('read_at')->nullable();
            $table->timestamps();

            $table->foreign('from_user_id')->references('id')->on('bitcoin_auth');
            $table->foreign('to_user_id')->references('id')->on('bitcoin_auth');
            $table->index(['to_user_id', 'is_read']);
            $table->index(['from_user_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('messages');
    }
};
