<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;
use Intervention\Image\Laravel\Facades\Image;

class ImageLibrary extends Model
{
    protected $table = 'image_library';

    protected $fillable = [
        'sha256_hash',
        'original_filename',
        'mime_type',
        'file_size',
        'width',
        'height',
        'storage_path',
        'thumbnail_path',
        'usage_count',
        'first_uploaded_at',
        'last_used_at',
        'uploaded_by_ip',
        'metadata'
    ];

    protected $casts = [
        'first_uploaded_at' => 'datetime',
        'last_used_at' => 'datetime',
        'metadata' => 'array'
    ];

    /**
     * Store a new image in the library or return existing one if already exists
     */
    public static function storeImage(UploadedFile $file, ?string $uploaderIp = null): self
    {
        // Calculate SHA256 hash of the file
        $hash = hash_file('sha256', $file->path());
        
        // Check if image already exists
        $existingImage = static::where('sha256_hash', $hash)->first();
        if ($existingImage) {
            $existingImage->increment('usage_count');
            $existingImage->update(['last_used_at' => now()]);
            return $existingImage;
        }

        // Get image dimensions
        $imageInfo = getimagesize($file->path());
        $width = $imageInfo[0] ?? 0;
        $height = $imageInfo[1] ?? 0;

        // Generate storage path based on hash
        $extension = $file->getClientOriginalExtension();
        $storagePath = "images/library/" . substr($hash, 0, 2) . "/" . substr($hash, 2, 2) . "/" . $hash . "." . $extension;
        $thumbnailPath = "images/library/thumbs/" . substr($hash, 0, 2) . "/" . substr($hash, 2, 2) . "/" . $hash . "_thumb.jpg";

        // Store the original file
        Storage::disk('public')->put($storagePath, file_get_contents($file->path()));

        // Create thumbnail
        static::createThumbnail($file->path(), storage_path('app/public/' . $thumbnailPath));

        // Extract metadata
        $metadata = static::extractMetadata($file->path());

        // Create database record
        return static::create([
            'sha256_hash' => $hash,
            'original_filename' => $file->getClientOriginalName(),
            'mime_type' => $file->getMimeType(),
            'file_size' => $file->getSize(),
            'width' => $width,
            'height' => $height,
            'storage_path' => $storagePath,
            'thumbnail_path' => $thumbnailPath,
            'usage_count' => 1,
            'first_uploaded_at' => now(),
            'last_used_at' => now(),
            'uploaded_by_ip' => $uploaderIp,
            'metadata' => $metadata
        ]);
    }

    /**
     * Get image by SHA256 hash
     */
    public static function getByHash(string $hash): ?self
    {
        return static::where('sha256_hash', $hash)->first();
    }

    /**
     * Mark image as used (increment usage count)
     */
    public function markAsUsed(): void
    {
        $this->increment('usage_count');
        $this->update(['last_used_at' => now()]);
    }

    /**
     * Get the full URL to the image
     */
    public function getImageUrl(): string
    {
        return Storage::disk('public')->url($this->storage_path);
    }

    /**
     * Get the full URL to the thumbnail
     */
    public function getThumbnailUrl(): string
    {
        return Storage::disk('public')->url($this->thumbnail_path);
    }

    /**
     * Get popular images (most used)
     */
    public static function getPopular(int $limit = 50)
    {
        return static::orderBy('usage_count', 'desc')
                    ->orderBy('last_used_at', 'desc')
                    ->limit($limit)
                    ->get();
    }

    /**
     * Get recently uploaded images
     */
    public static function getRecent(int $limit = 50)
    {
        return static::orderBy('first_uploaded_at', 'desc')
                    ->limit($limit)
                    ->get();
    }

    /**
     * Search images by filename or metadata
     */
    public static function search(string $query, int $limit = 50)
    {
        return static::where('original_filename', 'LIKE', "%{$query}%")
                    ->orWhere('metadata->description', 'LIKE', "%{$query}%")
                    ->orderBy('usage_count', 'desc')
                    ->limit($limit)
                    ->get();
    }

    /**
     * Create thumbnail for the image
     */
    protected static function createThumbnail(string $sourcePath, string $thumbnailPath): void
    {
        try {
            // Ensure directory exists
            $directory = dirname($thumbnailPath);
            if (!is_dir($directory)) {
                mkdir($directory, 0755, true);
            }

            // Create thumbnail using basic PHP (since Intervention Image may not be installed)
            $sourceImage = imagecreatefromstring(file_get_contents($sourcePath));
            if ($sourceImage === false) return;

            $originalWidth = imagesx($sourceImage);
            $originalHeight = imagesy($sourceImage);
            
            // Calculate thumbnail dimensions (max 150x150, maintain aspect ratio)
            $thumbSize = 150;
            $ratio = min($thumbSize / $originalWidth, $thumbSize / $originalHeight);
            $thumbWidth = (int)($originalWidth * $ratio);
            $thumbHeight = (int)($originalHeight * $ratio);
            
            $thumbnail = imagecreatetruecolor($thumbWidth, $thumbHeight);
            imagecopyresampled($thumbnail, $sourceImage, 0, 0, 0, 0, $thumbWidth, $thumbHeight, $originalWidth, $originalHeight);
            
            imagejpeg($thumbnail, $thumbnailPath, 85);
            
            imagedestroy($sourceImage);
            imagedestroy($thumbnail);
        } catch (\Exception $e) {
            // Silently fail if thumbnail creation fails
        }
    }

    /**
     * Extract metadata from image
     */
    protected static function extractMetadata(string $filePath): array
    {
        $metadata = [];
        
        try {
            // Get basic image info
            $imageInfo = getimagesize($filePath);
            if ($imageInfo) {
                $metadata['format'] = $imageInfo['mime'] ?? null;
                $metadata['channels'] = $imageInfo['channels'] ?? null;
                $metadata['bits'] = $imageInfo['bits'] ?? null;
            }

            // Try to get EXIF data
            if (function_exists('exif_read_data') && in_array(strtolower(pathinfo($filePath, PATHINFO_EXTENSION)), ['jpg', 'jpeg', 'tiff'])) {
                $exif = @exif_read_data($filePath);
                if ($exif) {
                    $metadata['exif'] = array_filter([
                        'make' => $exif['Make'] ?? null,
                        'model' => $exif['Model'] ?? null,
                        'datetime' => $exif['DateTime'] ?? null,
                        'orientation' => $exif['Orientation'] ?? null,
                    ]);
                }
            }
        } catch (\Exception $e) {
            // Silently fail if metadata extraction fails
        }

        return $metadata;
    }
}