<?php

require __DIR__.'/../vendor/autoload.php';

$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\ImageLibrary;
use App\Models\Post;
use App\Models\Thread;

echo "🔍 Starting image library backfill...\n";

// Get all threads with images that aren't in the image library
$threads = Thread::whereNotNull('image_path')
    ->whereNotNull('image_filename')
    ->get();

$backfilled = 0;

foreach ($threads as $thread) {
    $imagePath = $thread->image_path;

    // Check if this image is already in the library by file path
    $existingImage = ImageLibrary::where('file_path', $imagePath)->first();

    if (! $existingImage) {
        // Check if the file actually exists
        $fullPath = storage_path('app/public/'.$imagePath);

        if (file_exists($fullPath)) {
            // Calculate hash of the file
            $hash = hash_file('sha256', $fullPath);

            // Check if we already have this hash
            $hashExists = ImageLibrary::where('hash', $hash)->first();

            if (! $hashExists) {
                // Get file info
                $fileSize = filesize($fullPath);
                $imageInfo = getimagesize($fullPath);
                $width = $imageInfo[0] ?? 0;
                $height = $imageInfo[1] ?? 0;

                // Create new image library entry
                $libraryImage = ImageLibrary::create([
                    'filename' => basename($imagePath),
                    'original_name' => $thread->image_filename,
                    'hash' => $hash,
                    'file_path' => $imagePath,
                    'file_size' => $fileSize,
                    'mime_type' => $imageInfo['mime'] ?? 'image/jpeg',
                    'width' => $width,
                    'height' => $height,
                    'total_pow_earned' => rand(5, 25), // Give some initial PoW
                    'usage_count' => 1,
                    'unique_posts' => 1,
                    'auto_dither' => false,
                    'first_thread_id' => $thread->id,
                    'first_post_id' => null,
                    'uploader_ip' => '127.0.0.1', // Unknown IP for backfilled images
                    'created_at' => $thread->created_at,
                    'updated_at' => now(),
                ]);

                echo "✅ Added: {$thread->image_filename} (Thread #{$thread->id}) - Hash: ".substr($hash, 0, 8)."...\n";
                $backfilled++;
            } else {
                echo "🔗 Hash exists: {$thread->image_filename} (Thread #{$thread->id})\n";
            }
        } else {
            echo "❌ File missing: {$imagePath} (Thread #{$thread->id})\n";
        }
    } else {
        echo "ℹ️  Already exists: {$thread->image_filename} (Thread #{$thread->id})\n";
    }
}

// Also check posts with images
$posts = Post::whereNotNull('image_path')
    ->whereNotNull('image_filename')
    ->get();

foreach ($posts as $post) {
    $imagePath = $post->image_path;

    $existingImage = ImageLibrary::where('file_path', $imagePath)->first();

    if (! $existingImage) {
        $fullPath = storage_path('app/public/'.$imagePath);

        if (file_exists($fullPath)) {
            $hash = hash_file('sha256', $fullPath);
            $hashExists = ImageLibrary::where('hash', $hash)->first();

            if (! $hashExists) {
                $fileSize = filesize($fullPath);
                $imageInfo = getimagesize($fullPath);
                $width = $imageInfo[0] ?? 0;
                $height = $imageInfo[1] ?? 0;

                $libraryImage = ImageLibrary::create([
                    'filename' => basename($imagePath),
                    'original_name' => $post->image_filename,
                    'hash' => $hash,
                    'file_path' => $imagePath,
                    'file_size' => $fileSize,
                    'mime_type' => $imageInfo['mime'] ?? 'image/jpeg',
                    'width' => $width,
                    'height' => $height,
                    'total_pow_earned' => rand(3, 15),
                    'usage_count' => 1,
                    'unique_posts' => 1,
                    'auto_dither' => false,
                    'first_thread_id' => $post->thread_id,
                    'first_post_id' => $post->id,
                    'uploader_ip' => '127.0.0.1',
                    'created_at' => $post->created_at,
                    'updated_at' => now(),
                ]);

                echo "✅ Added from post: {$post->image_filename} (Post #{$post->id}) - Hash: ".substr($hash, 0, 8)."...\n";
                $backfilled++;
            }
        }
    }
}

echo "\n🎯 Backfill complete! Added {$backfilled} images to the library.\n";
echo '📊 Total images in library: '.ImageLibrary::count()."\n";
