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
        Schema::create('invite_codes', function (Blueprint $table) {
            $table->id();
            $table->string('code', 20)->unique();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('used_by')->nullable();
            $table->integer('uses_remaining')->default(1);
            $table->timestamp('expires_at')->nullable();
            $table->boolean('is_genesis')->default(false);
            $table->decimal('mining_bonus', 3, 2)->default(1.0);
            $table->timestamps();

            $table->foreign('created_by')->references('id')->on('bitcoin_auth')->onDelete('set null');
            $table->foreign('used_by')->references('id')->on('bitcoin_auth')->onDelete('set null');

            $table->index(['code']);
            $table->index(['created_by']);
            $table->index(['expires_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invite_codes');
    }
};
