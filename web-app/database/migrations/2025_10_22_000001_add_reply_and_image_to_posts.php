<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('posts', function (Blueprint $table) {
            if (!Schema::hasColumn('posts', 'parent_id')) {
                $table->foreignId('parent_id')->nullable()->constrained('posts')->nullOnDelete()->index();
            }
            if (!Schema::hasColumn('posts', 'image_path')) {
                $table->string('image_path')->nullable()->index();
            }
        });

        // If not present yet, these indexes help a lot:
        Schema::table('posts', function (Blueprint $table) {
            if (!Schema::hasIndex('posts', 'posts_thread_id_created_at_index')) {
                $table->index(['thread_id', 'created_at']);
            }
        });
    }

    public function down(): void {
        Schema::table('posts', function (Blueprint $table) {
            if (Schema::hasColumn('posts', 'image_path')) $table->dropColumn('image_path');
            if (Schema::hasColumn('posts', 'parent_id')) $table->dropConstrainedForeignId('parent_id');
        });
    }
};