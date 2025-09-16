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
        Schema::table('subscriptions', function (Blueprint $table) {
            // Add Apple User ID to prevent subscription abuse
            $table->string('apple_user_id')->nullable()->after('external_subscription_id');
            $table->string('apple_transaction_id')->nullable()->after('apple_user_id');
            $table->string('apple_original_transaction_id')->nullable()->after('apple_transaction_id');
            
            // Add index for faster lookups
            $table->index('apple_user_id');
            $table->index('apple_original_transaction_id');
            
            // Ensure one subscription per Apple user ID
            $table->unique('apple_user_id', 'unique_apple_user_subscription');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->dropIndex('unique_apple_user_subscription');
            $table->dropIndex(['apple_user_id']);
            $table->dropIndex(['apple_original_transaction_id']);
            $table->dropColumn(['apple_user_id', 'apple_transaction_id', 'apple_original_transaction_id']);
        });
    }
};