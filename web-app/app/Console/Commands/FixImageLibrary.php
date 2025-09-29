<?php

namespace App\Console\Commands;

use App\Models\ImageLibrary;
use Illuminate\Console\Command;

class FixImageLibrary extends Command
{
    protected $signature = 'imagelib:fix';
    protected $description = 'Fix broken image library entries and file paths';

    public function handle()
    {
        $this->info('🔧 Starting image library repair...');

        // Check for broken file paths
        $brokenImages = ImageLibrary::whereNotNull('file_path')->get()->filter(function ($image) {
            return !file_exists(public_path($image->file_path)) && 
                   !file_exists(public_path('storage/' . $image->file_path));
        });

        $this->warn("Found {$brokenImages->count()} images with missing files");

        foreach ($brokenImages as $image) {
            $this->line("❌ Missing: {$image->file_path} (ID: {$image->id})");
        }

        // Fix file path format for images
        $this->info('📁 Checking file path formats...');
        
        $fixedCount = 0;
        $images = ImageLibrary::all();
        
        foreach ($images as $image) {
            $fixed = false;
            
            // Check if file exists in expected location
            if (!file_exists(public_path($image->file_path))) {
                // Try alternative paths
                $altPaths = [
                    'storage/' . $image->file_path,
                    str_replace('forum/images/', 'storage/forum/images/', $image->file_path),
                    str_replace('storage/', '', $image->file_path),
                ];
                
                foreach ($altPaths as $altPath) {
                    if (file_exists(public_path($altPath))) {
                        $this->info("✅ Fixed path for image {$image->id}: {$image->file_path} -> {$altPath}");
                        $image->update(['file_path' => $altPath]);
                        $fixed = true;
                        $fixedCount++;
                        break;
                    }
                }
            }
            
            if (!$fixed && !file_exists(public_path($image->file_path))) {
                $this->warn("⚠️  Could not fix path for image {$image->id}: {$image->file_path}");
            }
        }

        $this->info("🔧 Fixed {$fixedCount} image file paths");

        // Validate database integrity
        $this->info('🔍 Validating database integrity...');
        
        $invalidImages = ImageLibrary::whereNull('hash')
            ->orWhereNull('file_path')
            ->orWhere('file_size', '<=', 0)
            ->get();

        if ($invalidImages->count() > 0) {
            $this->warn("Found {$invalidImages->count()} invalid database entries");
            
            $this->choice = $this->confirm('Delete invalid entries?');
            if ($this->choice) {
                foreach ($invalidImages as $invalid) {
                    $this->line("🗑️  Deleting invalid entry: ID {$invalid->id}");
                    $invalid->delete();
                }
            }
        }

        // Create forum/images directory if it doesn't exist
        $imageDir = public_path('forum/images');
        if (!file_exists($imageDir)) {
            mkdir($imageDir, 0755, true);
            $this->info("📁 Created directory: {$imageDir}");
        }

        // Check permissions
        if (!is_writable($imageDir)) {
            $this->warn("⚠️  Directory not writable: {$imageDir}");
        }

        $this->info('✅ Image library repair completed!');
        
        // Show summary
        $totalImages = ImageLibrary::count();
        $workingImages = ImageLibrary::get()->filter(function ($image) {
            return file_exists(public_path($image->file_path));
        })->count();
        
        $this->table(['Metric', 'Count'], [
            ['Total Images', $totalImages],
            ['Working Images', $workingImages],
            ['Broken Images', $totalImages - $workingImages],
            ['Fixed Paths', $fixedCount],
        ]);

        return 0;
    }
}