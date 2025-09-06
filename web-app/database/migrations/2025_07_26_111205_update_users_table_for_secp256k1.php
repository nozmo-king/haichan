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
        Schema::table('users', function (Blueprint $table) {
            // Drop unique index first to avoid SQLite issues
            try {
                $table->dropUnique(['public_key']);
            } catch (\Exception $e) {
                // Index might not exist or already dropped
            }
            
            // Remove old hash-based authentication columns
            if (Schema::hasColumn('users', 'private_key_hash')) {
                $table->dropColumn('private_key_hash');
            }
            
            // Remove public_key column 
            if (Schema::hasColumn('users', 'public_key')) {
                $table->dropColumn('public_key');
            }
        });
        
        // Add new columns in separate operation
        Schema::table('users', function (Blueprint $table) {
            // Add foreign key to allowed_public_keys table
            $table->foreignId('allowed_public_key_id')->constrained('allowed_public_keys')->onDelete('cascade');
            
            // Add session management
            $table->string('last_challenge')->nullable(); // store last challenge for replay protection
            $table->timestamp('challenge_expires_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['allowed_public_key_id']);
            $table->dropColumn(['allowed_public_key_id', 'last_challenge', 'challenge_expires_at']);
            
            // Restore old columns
            $table->string('public_key', 64)->unique();
            $table->string('private_key_hash', 64);
        });
    }
};
