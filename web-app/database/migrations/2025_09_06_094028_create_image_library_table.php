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
            $table->string('sha256_hash', 64)->unique();
            $table->string('original_filename');
            $table->string('mime_type');
            $table->integer('file_size');
            $table->integer('width');
            $table->integer('height');
            $table->string('storage_path');
            $table->string('thumbnail_path')->nullable();
            $table->integer('usage_count')->default(0);
            $table->timestamp('first_uploaded_at');
            $table->timestamp('last_used_at')->nullable();
            $table->string('uploaded_by_ip')->nullable();
            $table->json('metadata')->nullable(); // Store EXIF or other metadata
            $table->timestamps();
            
            $table->index('sha256_hash');
            $table->index('mime_type');
            $table->index('usage_count');
            $table->index('first_uploaded_at');
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