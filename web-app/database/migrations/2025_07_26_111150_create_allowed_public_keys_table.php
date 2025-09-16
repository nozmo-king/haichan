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
        Schema::create('allowed_public_keys', function (Blueprint $table) {
            $table->id();
            $table->string('public_key', 66)->unique(); // secp256k1 compressed public key (33 bytes = 66 hex chars)
            $table->string('label')->nullable(); // optional label/description for admin
            $table->boolean('is_active')->default(true); // allow admins to temporarily disable keys
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('allowed_public_keys');
    }
};
