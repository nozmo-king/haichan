<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::table('boards')->insert([
            'code' => 'ddl',
            'name' => 'Doodles',
            'description' => 'Draw and share your doodles! Create colorful artwork with 7 different colors.',
            'is_doodle_board' => true,
            'doodle_config' => json_encode([
                'colors' => [
                    '#000000', // Black
                    '#FF0000', // Red
                    '#00FF00', // Green
                    '#0000FF', // Blue
                    '#FFFF00', // Yellow
                    '#FF00FF', // Magenta
                    '#00FFFF'  // Cyan
                ],
                'max_redo_steps' => 3,
                'canvas_background' => '#FFFFFF'
            ]),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('boards')->where('code', 'ddl')->delete();
    }
};
