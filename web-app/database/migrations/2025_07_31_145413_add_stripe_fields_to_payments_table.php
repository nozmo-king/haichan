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
        Schema::table('payments', function (Blueprint $table) {
            // Add Stripe fields
            $table->string('stripe_checkout_session_id')->nullable()->after('gateway_payment_id');
            $table->string('stripe_payment_intent_id')->nullable()->after('stripe_checkout_session_id');
            
            // Make crypto fields nullable since we're adding Stripe
            $table->string('cryptocurrency')->nullable()->change();
            $table->decimal('crypto_amount', 20, 8)->nullable()->change();
            $table->string('wallet_address')->nullable()->change();
            $table->string('transaction_hash')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn(['stripe_checkout_session_id', 'stripe_payment_intent_id']);
            
            // Revert crypto fields to not nullable (this may cause issues if data exists)
            $table->string('cryptocurrency')->nullable(false)->change();
            $table->decimal('crypto_amount', 20, 8)->nullable(false)->change();
            $table->string('wallet_address')->nullable(false)->change();
        });
    }
};
