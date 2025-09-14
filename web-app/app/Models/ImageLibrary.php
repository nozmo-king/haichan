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
        'filename',
        'original_name',
        'hash',
        'file_path',
        'file_size',
        'mime_type',
        'width',
        'height',
        'total_pow_earned',
        'usage_count',
        'unique_posts',
        'auto_dither',
        'dither_settings',
        'first_thread_id',
        'first_post_id',
        'uploader_ip'
    ];

    protected $casts = [
        'dither_settings' => 'array',
        'auto_dither' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    /**
     * Store a new image in the library or return existing one if already exists
     */
    public static function storeImage(UploadedFile $file, ?string $uploaderIp = null, ?int $threadId = null, ?int $postId = null): self
    {
        // Calculate SHA256 hash of the file
        $hash = hash_file('sha256', $file->path());

        // Check if image already exists
        $existingImage = static::where('hash', $hash)->first();
        if ($existingImage) {
            $existingImage->increment('usage_count');
            $existingImage->increment('unique_posts');
            return $existingImage;
        }

        // Get image dimensions
        $imageInfo = getimagesize($file->path());
        $width = $imageInfo[0] ?? 0;
        $height = $imageInfo[1] ?? 0;

        // Generate unique filename
        $extension = $file->getClientOriginalExtension();
        $filename = time() . '_' . bin2hex(random_bytes(8)) . '.' . $extension;
        $filePath = "forum/images/" . $filename;

        // Store the file
        $file->storeAs('public/forum/images', $filename);

        // Create database record
        return static::create([
            'filename' => $filename,
            'original_name' => $file->getClientOriginalName(),
            'hash' => $hash,
            'file_path' => $filePath,
            'file_size' => $file->getSize(),
            'mime_type' => $file->getMimeType(),
            'width' => $width,
            'height' => $height,
            'total_pow_earned' => 0,
            'usage_count' => 1,
            'unique_posts' => 1,
            'auto_dither' => false,
            'first_thread_id' => $threadId,
            'first_post_id' => $postId,
            'uploader_ip' => $uploaderIp,
        ]);
    }

    /**
     * Get image by SHA256 hash
     */
    public static function getByHash(string $hash): ?self
    {
        return static::where('hash', $hash)->first();
    }

    /**
     * Mark image as used and award PoW points based on thread/post PoW
     */
    public function markAsUsed(int $powPoints = 1): void
    {
        $this->increment('usage_count');
        $this->increment('unique_posts');
        $this->increment('total_pow_earned', $powPoints);
    }

    /**
     * Get the full URL to the image
     */
    public function getImageUrl(): string
    {
        return asset('storage/' . $this->file_path);
    }

    /**
     * Award PoW to this image based on post/thread performance
     */
    public function awardPoW(int $points): void
    {
        $this->increment('total_pow_earned', $points);
    }

    /**
     * Get ever-shifting library - images ranked by PoW and usage with randomization
     */
    public static function getShiftingLibrary(int $limit = 100)
    {
        // Get images with weighted random selection based on PoW and usage
        // Use RANDOM() for SQLite instead of RAND() for MySQL
        return static::selectRaw('*, (total_pow_earned * 2 + usage_count) as weight')
                    ->orderByRaw('weight DESC, RANDOM()')
                    ->limit($limit)
                    ->get();
    }

    /**
     * Get top PoW earning images
     */
    public static function getTopPoWEarners(int $limit = 50)
    {
        return static::orderBy('total_pow_earned', 'desc')
                    ->orderBy('usage_count', 'desc')
                    ->limit($limit)
                    ->get();
    }

    /**
     * Get most popular images (by usage)
     */
    public static function getMostPopular(int $limit = 50)
    {
        return static::orderBy('usage_count', 'desc')
                    ->orderBy('unique_posts', 'desc')
                    ->limit($limit)
                    ->get();
    }

    /**
     * Get recently uploaded images
     */
    public static function getRecent(int $limit = 50)
    {
        return static::orderBy('created_at', 'desc')
                    ->limit($limit)
                    ->get();
    }

    /**
     * Search images by filename
     */
    public static function search(string $query, int $limit = 50)
    {
        return static::where('original_name', 'LIKE', "%{$query}%")
                    ->orderBy('total_pow_earned', 'desc')
                    ->orderBy('usage_count', 'desc')
                    ->limit($limit)
                    ->get();
    }

    /**
     * Get images for the mouseover-reactive grid
     */
    public static function getMovingLibrary(int $limit = 200)
    {
        return static::selectRaw('*, (total_pow_earned + usage_count * 10) as power_level')
                    ->orderBy('power_level', 'desc')
                    ->limit($limit)
                    ->get()
                    ->shuffle(); // Randomize initial positions
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