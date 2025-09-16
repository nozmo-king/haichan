<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->bigInteger('pow_points')->default(0)->after('email');
            $table->timestamp('last_mining_activity')->nullable()->after('pow_points');
            $table->string('bitcoin_address', 62)->nullable()->unique()->after('last_mining_activity');
            $table->boolean('mining_enabled')->default(true)->after('bitcoin_address');
            
            $table->index(['pow_points', 'created_at']);
            $table->index('bitcoin_address');
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['pow_points', 'created_at']);
            $table->dropIndex(['bitcoin_address']);
            $table->dropColumn(['pow_points', 'last_mining_activity', 'bitcoin_address', 'mining_enabled']);
        });
    }
};
