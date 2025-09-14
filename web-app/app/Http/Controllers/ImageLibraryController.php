<?php

namespace App\Http\Controllers;

use App\Models\ImageLibrary;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;

class ImageLibraryController extends Controller
{
    public function index()
    {
        $images = ImageLibrary::getShiftingLibrary(50);

        return view('image-library.index', compact('images'));
    }

    public function mine(Request $request)
    {
        $request->validate([
            'image_id' => 'required|exists:image_library,id',
            'hash_rate' => 'nullable|integer|min:0|max:10000'
        ]);

        $image = ImageLibrary::findOrFail($request->image_id);

        // Award PoW points based on hash rate and randomness
        $hashRate = $request->hash_rate ?? rand(100, 1000);
        $basePoints = max(1, floor($hashRate / 100));
        $bonusPoints = rand(0, 5); // Random bonus 0-5
        $totalPoints = $basePoints + $bonusPoints;

        // Apply mining bonus for rare hash patterns
        if (str_starts_with($image->hash, '000')) {
            $totalPoints *= 5; // Legendary bonus
        } elseif (str_starts_with($image->hash, '21e8')) {
            $totalPoints *= 2; // Common pattern bonus
        }

        $image->awardPoW($totalPoints);

        return response()->json([
            'success' => true,
            'points' => $totalPoints,
            'new_total' => $image->fresh()->total_pow_earned,
            'message' => "Mined {$totalPoints} PoW points!"
        ]);
    }

    public function upload(Request $request)
    {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,jpg,png,gif|max:10240', // 10MB max
            'auto_dither' => 'boolean'
        ]);

        $image = $request->file('image');
        $autoDither = $request->boolean('auto_dither');

        // Store image and get library entry
        $libraryImage = ImageLibrary::storeImage(
            $image,
            $request->ip(),
            null, // thread_id
            null  // post_id
        );

        // Apply auto-dithering if requested
        if ($autoDither) {
            $this->applyDithering($libraryImage);
        }

        // Award initial PoW points for uploading
        $uploadPoints = rand(5, 15);
        $libraryImage->awardPoW($uploadPoints);

        return response()->json([
            'success' => true,
            'image_id' => $libraryImage->id,
            'hash' => $libraryImage->hash,
            'pow_points' => $uploadPoints,
            'auto_dithered' => $autoDither,
            'message' => 'Image uploaded successfully!'
        ]);
    }

    public function fullImage($id)
    {
        $image = ImageLibrary::findOrFail($id);

        $path = storage_path('app/public/' . $image->file_path);

        if (!file_exists($path)) {
            abort(404);
        }

        return response()->file($path);
    }

    public function download($id)
    {
        $image = ImageLibrary::findOrFail($id);

        $path = storage_path('app/public/' . $image->file_path);

        if (!file_exists($path)) {
            abort(404);
        }

        // Increment usage count for downloads
        $image->markAsUsed(1);

        return response()->download($path, $image->original_name);
    }

    public function getByHash($hash)
    {
        $image = ImageLibrary::getByHash($hash);

        if (!$image) {
            return response()->json(['error' => 'Image not found'], 404);
        }

        return response()->json([
            'id' => $image->id,
            'filename' => $image->original_name,
            'hash' => $image->hash,
            'total_pow' => $image->total_pow_earned,
            'usage_count' => $image->usage_count,
            'url' => $image->getImageUrl()
        ]);
    }

    public function getStats()
    {
        $stats = [
            'total_images' => ImageLibrary::count(),
            'total_pow' => ImageLibrary::sum('total_pow_earned'),
            'total_usage' => ImageLibrary::sum('usage_count'),
            'top_earners' => ImageLibrary::getTopPoWEarners(5),
            'most_popular' => ImageLibrary::getMostPopular(5),
            'recent_uploads' => ImageLibrary::getRecent(5)
        ];

        return response()->json($stats);
    }

    public function search(Request $request)
    {
        $request->validate([
            'q' => 'required|string|min:1|max:100'
        ]);

        $images = ImageLibrary::search($request->q, 25);

        return response()->json([
            'query' => $request->q,
            'results' => $images,
            'count' => $images->count()
        ]);
    }

    /**
     * Apply dithering effect to an image
     */
    protected function applyDithering(ImageLibrary $libraryImage)
    {
        $sourcePath = storage_path('app/public/' . $libraryImage->file_path);

        if (!file_exists($sourcePath)) {
            return false;
        }

        try {
            // Create image resource
            $image = imagecreatefromstring(file_get_contents($sourcePath));
            if ($image === false) return false;

            $width = imagesx($image);
            $height = imagesy($image);

            // Apply Floyd-Steinberg dithering
            for ($y = 0; $y < $height; $y++) {
                for ($x = 0; $x < $width; $x++) {
                    $rgb = imagecolorat($image, $x, $y);

                    $r = ($rgb >> 16) & 0xFF;
                    $g = ($rgb >> 8) & 0xFF;
                    $b = $rgb & 0xFF;

                    // Convert to grayscale and apply dithering
                    $gray = (int)($r * 0.299 + $g * 0.587 + $b * 0.114);
                    $newGray = $gray < 128 ? 0 : 255;

                    $error = $gray - $newGray;

                    // Set the new pixel
                    $newColor = imagecolorallocate($image, $newGray, $newGray, $newGray);
                    imagesetpixel($image, $x, $y, $newColor);

                    // Distribute error to neighboring pixels (Floyd-Steinberg)
                    $this->distributeError($image, $x + 1, $y, $width, $height, $error * 7 / 16);
                    $this->distributeError($image, $x - 1, $y + 1, $width, $height, $error * 3 / 16);
                    $this->distributeError($image, $x, $y + 1, $width, $height, $error * 5 / 16);
                    $this->distributeError($image, $x + 1, $y + 1, $width, $height, $error * 1 / 16);
                }
            }

            // Save dithered image
            $ditheredPath = str_replace('.', '_dithered.', $sourcePath);
            imagejpeg($image, $ditheredPath, 85);
            imagedestroy($image);

            // Update database record
            $libraryImage->update([
                'auto_dither' => true,
                'dither_settings' => [
                    'method' => 'floyd_steinberg',
                    'applied_at' => now(),
                    'original_path' => $libraryImage->file_path
                ]
            ]);

            // Update file path to dithered version
            $libraryImage->file_path = str_replace('public/', '', $ditheredPath);
            $libraryImage->save();

            return true;

        } catch (\Exception $e) {
            \Log::error('Dithering failed: ' . $e->getMessage());
            return false;
        }
    }

    protected function distributeError($image, $x, $y, $width, $height, $error)
    {
        if ($x >= 0 && $x < $width && $y >= 0 && $y < $height) {
            $rgb = imagecolorat($image, $x, $y);
            $r = ($rgb >> 16) & 0xFF;
            $g = ($rgb >> 8) & 0xFF;
            $b = $rgb & 0xFF;

            $gray = (int)($r * 0.299 + $g * 0.587 + $b * 0.114);
            $newGray = max(0, min(255, $gray + $error));

            $newColor = imagecolorallocate($image, $newGray, $newGray, $newGray);
            imagesetpixel($image, $x, $y, $newColor);
        }
    }

    /**
     * Create ever-shifting arrangement based on PoW activity
     */
    public function getShiftingArrangement()
    {
        $images = ImageLibrary::selectRaw('
                id, original_name, hash, total_pow_earned, usage_count, file_path,
                (total_pow_earned * 3 + usage_count * 2 + RANDOM() * 100) as shift_weight
            ')
            ->orderBy('shift_weight', 'desc')
            ->limit(100)
            ->get();

        return response()->json([
            'arrangement' => $images,
            'timestamp' => now(),
            'shift_factor' => rand(1, 10)
        ]);
    }
}