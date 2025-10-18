<?php

namespace App\Services;

use App\Helpers\FilenameHelper;
use App\Models\ImageLibrary;
use App\Services\FilenamePatternService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Laravel\Facades\Image;

class ImageIndexingService
{
    /**
     * Process and index an uploaded image
     */
    public function processAndIndexImage(
        UploadedFile $file,
        ?string $threadId = null,
        ?string $postId = null,
        ?string $uploaderIp = null
    ): array {
        try {
            // Generate SHA-256 hash of file content
            $fileContent = file_get_contents($file->getPathname());
            $fileHash = hash('sha256', $fileContent);

            // Check if image already exists in library
            $existingImage = ImageLibrary::where('hash', $fileHash)->first();

            if ($existingImage) {
                // Update usage statistics
                $existingImage->increment('usage_count');

                if ($threadId) {
                    // Check if this is a new unique usage in this thread
                    $existingUsage = \DB::table('posts')
                        ->where('thread_id', $threadId)
                        ->where('image_hash', $fileHash)
                        ->exists();

                    if (! $existingUsage) {
                        $existingImage->increment('unique_posts');
                    }
                }

                Log::info('Image reused from library', [
                    'hash' => $fileHash,
                    'image_id' => $existingImage->id,
                    'usage_count' => $existingImage->usage_count,
                ]);

                return [
                    'success' => true,
                    'image_id' => $existingImage->id,
                    'hash' => $fileHash,
                    'file_path' => $existingImage->file_path,
                    'reused' => true,
                ];
            }

            // Generate unique filename using themed patterns
            $useThemedGenerator = rand(0, 1); // 50% chance to use themed vs standard randomizer
            
            if ($useThemedGenerator) {
                $filename = FilenamePatternService::getThemedFilenameWithExtension($file->getClientOriginalName());
            } else {
                $randomStyle = ['aesthetic', 'technical', 'mystical', 'minimal'][array_rand(['aesthetic', 'technical', 'mystical', 'minimal'])];
                $filename = FilenameHelper::randomizeFilename($file->getClientOriginalName(), $randomStyle);
            }

            // Store file directly in public directory for web accessibility
            $filePath = 'forum/images/'.$filename;
            $publicPath = public_path('forum/images');
            
            // Ensure directory exists
            if (!file_exists($publicPath)) {
                mkdir($publicPath, 0755, true);
            }
            
            // Save file directly to public directory
            file_put_contents($publicPath . '/' . $filename, $fileContent);

            // Get image dimensions if it's an image
            $dimensions = $this->getImageDimensions($file);

            // Create library entry
            $imageRecord = ImageLibrary::create([
                'filename' => $filename,
                'original_name' => $file->getClientOriginalName(),
                'hash' => $fileHash,
                'file_path' => $filePath,
                'file_size' => $file->getSize(),
                'mime_type' => $file->getMimeType(),
                'width' => $dimensions['width'] ?? null,
                'height' => $dimensions['height'] ?? null,
                'usage_count' => 1,
                'unique_posts' => 1,
                'first_thread_id' => $threadId,
                'first_post_id' => $postId,
                'uploader_ip' => $uploaderIp,
                'total_pow_earned' => 0,
                'auto_dither' => false,
                'dither_settings' => null,
            ]);

            Log::info('New image indexed in library', [
                'image_id' => $imageRecord->id,
                'hash' => $fileHash,
                'filename' => $filename,
                'size' => $file->getSize(),
                'thread_id' => $threadId,
            ]);

            return [
                'success' => true,
                'image_id' => $imageRecord->id,
                'hash' => $fileHash,
                'file_path' => $filePath,
                'reused' => false,
            ];

        } catch (\Exception $e) {
            Log::error('Failed to process and index image', [
                'error' => $e->getMessage(),
                'file_name' => $file->getClientOriginalName(),
                'file_size' => $file->getSize(),
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Process image using existing hash (from image library)
     */
    public function processImageByHash(string $imageHash, ?string $threadId = null, ?string $postId = null): array
    {
        try {
            $image = ImageLibrary::where('hash', $imageHash)->first();

            if (! $image) {
                return [
                    'success' => false,
                    'error' => 'Image hash not found in library',
                ];
            }

            // Update usage statistics
            $image->increment('usage_count');

            if ($threadId) {
                // Check if this is a new unique usage
                $existingUsage = \DB::table('posts')
                    ->where('thread_id', $threadId)
                    ->where('image_hash', $imageHash)
                    ->exists();

                if (! $existingUsage) {
                    $image->increment('unique_posts');
                }
            }

            Log::info('Image reused via hash', [
                'hash' => $imageHash,
                'image_id' => $image->id,
                'usage_count' => $image->usage_count,
            ]);

            return [
                'success' => true,
                'image_id' => $image->id,
                'hash' => $imageHash,
                'file_path' => $image->file_path,
                'reused' => true,
            ];

        } catch (\Exception $e) {
            Log::error('Failed to process image by hash', [
                'error' => $e->getMessage(),
                'hash' => $imageHash,
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Get image dimensions if possible
     */
    private function getImageDimensions(UploadedFile $file): array
    {
        try {
            // Check if it's an image MIME type
            $mimeType = $file->getMimeType();
            if (! str_starts_with($mimeType, 'image/')) {
                return ['width' => null, 'height' => null];
            }

            // Try to get dimensions using getimagesize
            $imageInfo = getimagesize($file->getPathname());

            if ($imageInfo !== false) {
                return [
                    'width' => $imageInfo[0],
                    'height' => $imageInfo[1],
                ];
            }

            return [];

        } catch (\Exception $e) {
            Log::warning('Failed to get image dimensions', [
                'error' => $e->getMessage(),
                'file' => $file->getClientOriginalName(),
            ]);

            return [];
        }
    }

    /**
     * Generate clickable hash for display
     */
    public static function formatHashForDisplay(string $hash, int $displayLength = 8): string
    {
        return substr($hash, 0, $displayLength).'...';
    }

    /**
     * Validate image hash format
     */
    public static function isValidHash(string $hash): bool
    {
        return preg_match('/^[a-f0-9]{64}$/i', $hash) === 1;
    }
}
