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
        Schema::table('bitcoin_auth', function (Blueprint $table) {
            // Profile fields
            $table->text('bio')->nullable()->after('username');
            $table->string('location', 100)->nullable()->after('bio');
            $table->string('website', 255)->nullable()->after('location');
            $table->string('avatar_hash', 64)->nullable()->after('website');
            $table->string('display_name', 100)->nullable()->after('avatar_hash');
            $table->string('tripcode', 20)->nullable()->after('display_name');
            $table->json('social_links')->nullable()->after('tripcode');
            $table->boolean('show_email')->default(false)->after('social_links');
            $table->string('email', 255)->nullable()->after('show_email');
            $table->string('timezone', 50)->default('UTC')->after('email');
            $table->text('signature')->nullable()->after('timezone');
            $table->boolean('profile_public')->default(true)->after('signature');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bitcoin_auth', function (Blueprint $table) {
            $table->dropColumn([
                'bio', 'location', 'website', 'avatar_hash', 'display_name',
                'tripcode', 'social_links', 'show_email', 'email', 'timezone',
                'signature', 'profile_public'
            ]);
        });
    }
};