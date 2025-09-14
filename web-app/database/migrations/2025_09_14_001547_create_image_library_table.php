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
        Schema::create('image_library', function (Blueprint $table) {
            $table->id();
            $table->string('filename')->unique();
            $table->string('original_name');
            $table->string('hash', 64)->unique(); // SHA256 hash of file content
            $table->string('file_path');
            $table->integer('file_size');
            $table->string('mime_type');
            $table->integer('width')->nullable();
            $table->integer('height')->nullable();

            // PoW tracking
            $table->unsignedBigInteger('total_pow_earned')->default(0);
            $table->unsignedBigInteger('usage_count')->default(0);
            $table->unsignedBigInteger('unique_posts')->default(0);

            // Auto-dithering options
            $table->boolean('auto_dither')->default(false);
            $table->json('dither_settings')->nullable(); // Store dithering parameters

            // First upload tracking
            $table->unsignedBigInteger('first_thread_id')->nullable();
            $table->unsignedBigInteger('first_post_id')->nullable();
            $table->string('uploader_ip', 45);

            $table->timestamps();

            $table->index(['total_pow_earned', 'usage_count']);
            $table->index(['hash']);
            $table->index(['created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('image_library');
    }
};
