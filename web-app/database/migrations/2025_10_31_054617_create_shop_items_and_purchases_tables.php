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
        Schema::create('shop_items', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description');
            $table->integer('price');
            $table->string('type'); // badge, color, boost, feature, etc
            $table->json('metadata')->nullable(); // Store item-specific data
            $table->boolean('is_active')->default(true);
            $table->integer('stock')->nullable(); // null = unlimited
            $table->integer('level_required')->default(1);
            $table->string('icon')->nullable();
            $table->timestamps();
        });

        Schema::create('shop_purchases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('bitcoin_auth')->onDelete('cascade');
            $table->foreignId('shop_item_id')->constrained()->onDelete('cascade');
            $table->integer('price_paid');
            $table->boolean('is_active')->default(true); // For toggleable items
            $table->timestamp('expires_at')->nullable(); // For temporary items
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shop_purchases');
        Schema::dropIfExists('shop_items');
    }
};
