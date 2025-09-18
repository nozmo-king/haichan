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
        // First, migrate data from old columns to new columns (if they exist)
        if (Schema::hasColumn('image_library', 'sha256_hash') && Schema::hasColumn('image_library', 'filename')) {
            \DB::statement("UPDATE image_library SET
                filename = CASE
                    WHEN filename IS NULL THEN SUBSTR(storage_path, INSTR(storage_path, '/') + 1)
                    ELSE filename
                END,
                original_name = CASE
                    WHEN original_name IS NULL THEN original_filename
                    ELSE original_name
                END,
                hash = CASE
                    WHEN hash IS NULL THEN sha256_hash
                    ELSE hash
                END,
                file_path = CASE
                    WHEN file_path IS NULL THEN storage_path
                    ELSE file_path
                END,
                usage_count_new = CASE
                    WHEN usage_count_new = 0 THEN usage_count
                    ELSE usage_count_new
                END,
                uploader_ip = CASE
                    WHEN uploader_ip IS NULL THEN uploaded_by_ip
                    ELSE uploader_ip
                END
            WHERE sha256_hash IS NOT NULL");
        }

        // Add missing new columns if they don't exist
        Schema::table('image_library', function (Blueprint $table) {
            if (!Schema::hasColumn('image_library', 'filename')) {
                $table->string('filename')->nullable()->after('id');
            }
            if (!Schema::hasColumn('image_library', 'original_name')) {
                $table->string('original_name')->nullable()->after('filename');
            }
            if (!Schema::hasColumn('image_library', 'hash')) {
                $table->string('hash', 64)->nullable()->after('original_name');
            }
            if (!Schema::hasColumn('image_library', 'file_path')) {
                $table->string('file_path')->nullable()->after('hash');
            }
            if (!Schema::hasColumn('image_library', 'total_pow_earned')) {
                $table->unsignedBigInteger('total_pow_earned')->default(0)->after('height');
            }
            if (!Schema::hasColumn('image_library', 'usage_count_new')) {
                $table->unsignedBigInteger('usage_count_new')->default(0)->after('total_pow_earned');
            }
            if (!Schema::hasColumn('image_library', 'unique_posts')) {
                $table->unsignedBigInteger('unique_posts')->default(0)->after('usage_count_new');
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

        // Drop old columns one by one with error handling
        $oldColumns = ['sha256_hash', 'original_filename', 'storage_path', 'thumbnail_path',
                      'first_uploaded_at', 'last_used_at', 'uploaded_by_ip', 'metadata'];

        foreach ($oldColumns as $column) {
            if (Schema::hasColumn('image_library', $column)) {
                Schema::table('image_library', function (Blueprint $table) use ($column) {
                    $table->dropColumn($column);
                });
            }
        }

        // Handle usage_count rename carefully
        if (Schema::hasColumn('image_library', 'usage_count') && Schema::hasColumn('image_library', 'usage_count_new')) {
            Schema::table('image_library', function (Blueprint $table) {
                $table->dropColumn('usage_count');
            });
        }

        if (Schema::hasColumn('image_library', 'usage_count_new')) {
            Schema::table('image_library', function (Blueprint $table) {
                $table->renameColumn('usage_count_new', 'usage_count');
            });
        }

        // Add new indexes
        Schema::table('image_library', function (Blueprint $table) {
            try {
                $table->unique('hash');
            } catch (\Exception $e) {
                // Index might already exist
            }

            try {
                $table->index(['total_pow_earned', 'usage_count']);
            } catch (\Exception $e) {
                // Index might already exist
            }

            try {
                $table->index(['hash']);
            } catch (\Exception $e) {
                // Index might already exist
            }

            try {
                $table->index(['created_at']);
            } catch (\Exception $e) {
                // Index might already exist
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // This is a cleanup migration, rollback would be complex
        // In practice, this should not be rolled back
        throw new \Exception('This cleanup migration cannot be rolled back safely');
    }
};
