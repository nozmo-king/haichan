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
            'hash_rate' => 'nullable|integer|min:0|max:10000',
            'proof_hash' => 'nullable|string|size:64',
            'nonce' => 'nullable|integer'
        ]);

        $image = ImageLibrary::findOrFail($request->image_id);

        // If proper PoW proof is provided, verify it first
        if ($request->has('proof_hash') && $request->has('nonce')) {
            $proofController = new \App\Http\Controllers\ProofOfWorkController();

            // Create data string for image mining
            $data = "image_mine:{$image->id}:{$image->hash}:{$request->nonce}";
            $pattern = $this->detectHashPattern($request->proof_hash);

            $verification = $proofController->verifyProof(
                $data,
                $request->nonce,
                $request->proof_hash,
                $pattern
            );

            if (!$verification['valid']) {
                return response()->json([
                    'success' => false,
                    'message' => $verification['error'] ?? 'Proof of work verification failed'
                ], 400);
            }

            // Award points based on actual PoW pattern
            $totalPoints = $this->calculateImageMiningPoints($request->proof_hash, $pattern);

            // Create ProofOfWork record
            \App\Models\ProofOfWork::create([
                'thread_id' => null, // Image mining doesn't relate to threads
                'hash' => $request->proof_hash,
                'nonce' => $request->nonce,
                'data' => $data,
                'pattern' => $pattern,
                'points' => $totalPoints,
                'ip_address' => $request->ip(),
                'verified_at' => now()
            ]);

        } else {
            // Fallback to simplified mining for UI interactions without real PoW
            $hashRate = $request->hash_rate ?? rand(500, 2000);
            $basePoints = max(5, floor($hashRate / 50));
            $bonusPoints = rand(5, 25);
            $totalPoints = $basePoints + $bonusPoints;

            // Apply multipliers based on image's file hash patterns
            if (str_starts_with($image->hash, '000')) {
                $totalPoints *= 10;
            } elseif (str_starts_with($image->hash, '21e8')) {
                $totalPoints *= 3;
            } elseif (str_starts_with($image->hash, '666')) {
                $totalPoints *= 15;
            } elseif (str_starts_with($image->hash, 'dead')) {
                $totalPoints *= 8;
            }
        }

        // Rare chance for MEGA JACKPOT
        $jackpot = rand(1, 100) <= 5;
        if ($jackpot) {
            $totalPoints *= 5;
        }

        $image->awardPoW($totalPoints);

        $message = "Mined {$totalPoints} PoW points!";
        if ($jackpot) {
            $message = "🎰 MEGA JACKPOT! {$totalPoints} PoW points!";
        } elseif (str_starts_with($image->hash, '000')) {
            $message = "💎 LEGENDARY HASH! {$totalPoints} PoW points!";
        } elseif (str_starts_with($image->hash, '666')) {
            $message = "😈 CURSED HASH! {$totalPoints} PoW points!";
        } elseif (str_starts_with($image->hash, 'dead')) {
            $message = "💀 DEATH HASH! {$totalPoints} PoW points!";
        }

        return response()->json([
            'success' => true,
            'points' => $totalPoints,
            'new_total' => $image->fresh()->total_pow_earned,
            'message' => $message,
            'jackpot' => $jackpot,
            'hash_pattern' => substr($image->hash, 0, 8),
            'verified_pow' => $request->has('proof_hash')
        ]);
    }

    /**
     * Detect hash pattern for PoW verification
     */
    private function detectHashPattern($hash)
    {
        $hash = strtolower($hash);
        if (str_starts_with($hash, '000021e8')) return '000021e8';
        if (str_starts_with($hash, '21e8000')) return '21e8000';
        if (str_starts_with($hash, '21e800')) return '21e800';
        if (str_starts_with($hash, '21e80')) return '21e80';
        if (str_starts_with($hash, '21e8')) return '21e8';
        if (str_starts_with($hash, '000')) return '000'; // Special case for legendary
        if (str_starts_with($hash, '666')) return '666'; // Special case for cursed
        if (str_starts_with($hash, 'dead')) return 'dead'; // Special case for death
        return '21';
    }

    /**
     * Calculate points for verified image mining
     */
    private function calculateImageMiningPoints($hash, $pattern)
    {
        $basePoints = [
            '21' => 1,
            '21e8' => 5,
            '21e80' => 25,
            '21e800' => 125,
            '21e8000' => 625,
            '000021e8' => 3125,
            '000' => 500,  // Legendary
            '666' => 750,  // Cursed
            'dead' => 400  // Death
        ];

        // Image mining gets bonus multiplier for special engagement
        $points = ($basePoints[$pattern] ?? 1) * 2;

        // Additional bonus for rare image hash patterns
        return $points + rand(5, 50);
    }

    public function upload(Request $request)
    {
        $request->validate([
            'image' => 'required|file|mimes:jpeg,png,jpg,gif,webp,webm,mp4,mov,avi,svg,bmp,tiff,avif,heic,heif|max:25600', // 25MB max
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