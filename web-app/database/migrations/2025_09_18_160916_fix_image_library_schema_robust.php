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
        // Drop all problematic indexes first to avoid conflicts
        $indexesToDrop = [
            'image_library_sha256_hash_unique',
            'image_library_sha256_hash_index',
            'image_library_mime_type_index',
            'image_library_usage_count_index',
            'image_library_first_uploaded_at_index',
            'image_library_total_pow_earned_usage_count_index',
            'image_library_hash_unique',
            'image_library_hash_index',
            'image_library_created_at_index'
        ];

        foreach ($indexesToDrop as $index) {
            try {
                \DB::statement("DROP INDEX IF EXISTS `{$index}`");
            } catch (\Exception $e) {
                // Continue if index doesn't exist
            }
        }

        // Get current columns to determine state
        $columns = \Schema::getColumnListing('image_library');
        $hasOldSchema = in_array('sha256_hash', $columns);
        $hasNewSchema = in_array('hash', $columns);

        // Add missing new columns if they don't exist
        if (!$hasNewSchema) {
            Schema::table('image_library', function (Blueprint $table) {
                $table->string('filename')->nullable()->after('id');
                $table->string('original_name')->nullable()->after('filename');
                $table->string('hash', 64)->nullable()->after('original_name');
                $table->string('file_path')->nullable()->after('hash');
                $table->unsignedBigInteger('total_pow_earned')->default(0)->after('height');
                $table->unsignedBigInteger('unique_posts')->default(0)->after('total_pow_earned');
                $table->boolean('auto_dither')->default(false)->after('unique_posts');
                $table->json('dither_settings')->nullable()->after('auto_dither');
                $table->unsignedBigInteger('first_thread_id')->nullable()->after('dither_settings');
                $table->unsignedBigInteger('first_post_id')->nullable()->after('first_thread_id');
                $table->string('uploader_ip', 45)->nullable()->after('first_post_id');
            });
        }

        // Migrate data if old schema exists
        if ($hasOldSchema && $hasNewSchema) {
            \DB::statement("UPDATE image_library SET
                filename = COALESCE(filename, SUBSTR(storage_path, INSTR(storage_path, '/') + 1)),
                original_name = COALESCE(original_name, original_filename),
                hash = COALESCE(hash, sha256_hash),
                file_path = COALESCE(file_path, storage_path),
                uploader_ip = COALESCE(uploader_ip, uploaded_by_ip),
                unique_posts = COALESCE(unique_posts, 1)
            WHERE sha256_hash IS NOT NULL");
        }

        // Drop old columns if they exist
        $oldColumns = ['sha256_hash', 'original_filename', 'storage_path', 'thumbnail_path',
                      'first_uploaded_at', 'last_used_at', 'uploaded_by_ip', 'metadata'];

        foreach ($oldColumns as $column) {
            if (Schema::hasColumn('image_library', $column)) {
                Schema::table('image_library', function (Blueprint $table) use ($column) {
                    $table->dropColumn($column);
                });
            }
        }

        // Add missing columns that might not be present in some states
        Schema::table('image_library', function (Blueprint $table) {
            if (!Schema::hasColumn('image_library', 'total_pow_earned')) {
                $table->unsignedBigInteger('total_pow_earned')->default(0)->after('height');
            }
            if (!Schema::hasColumn('image_library', 'unique_posts')) {
                $table->unsignedBigInteger('unique_posts')->default(0)->after('total_pow_earned');
            }
            if (!Schema::hasColumn('image_library', 'auto_dither')) {
                $table->boolean('auto_dither')->default(false)->after('unique_posts');
            }
            if (!Schema::hasColumn('image_library', 'dither_settings')) {
                $table->json('dither_settings')->nullable()->after('auto_dither');
            }
            if (!Schema::hasColumn('image_library', 'first_thread_id')) {
                $table->unsignedBigInteger('first_thread_id')->nullable()->after('dither_settings');
            }
            if (!Schema::hasColumn('image_library', 'first_post_id')) {
                $table->unsignedBigInteger('first_post_id')->nullable()->after('first_thread_id');
            }
            if (!Schema::hasColumn('image_library', 'uploader_ip')) {
                $table->string('uploader_ip', 45)->nullable()->after('first_post_id');
            }
        });

        // Recreate indexes with error handling
        try {
            \DB::statement("CREATE UNIQUE INDEX IF NOT EXISTS image_library_hash_unique ON image_library (hash)");
        } catch (\Exception $e) {
            // Index might already exist
        }

        try {
            \DB::statement("CREATE INDEX IF NOT EXISTS image_library_total_pow_earned_usage_count_index ON image_library (total_pow_earned, usage_count)");
        } catch (\Exception $e) {
            // Index might already exist
        }

        try {
            \DB::statement("CREATE INDEX IF NOT EXISTS image_library_hash_index ON image_library (hash)");
        } catch (\Exception $e) {
            // Index might already exist
        }

        try {
            \DB::statement("CREATE INDEX IF NOT EXISTS image_library_created_at_index ON image_library (created_at)");
        } catch (\Exception $e) {
            // Index might already exist
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // This migration is designed to be non-reversible for safety
        // Rolling back could cause data loss
    }
};
