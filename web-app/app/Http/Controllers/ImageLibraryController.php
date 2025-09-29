<?php

namespace App\Http\Controllers;

use App\Models\ImageLibrary;
use Illuminate\Http\Request;

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
            'nonce' => 'nullable|integer',
        ]);

        $image = ImageLibrary::findOrFail($request->image_id);

        // If proper PoW proof is provided, verify it first
        if ($request->has('proof_hash') && $request->has('nonce')) {
            $proofController = new \App\Http\Controllers\ProofOfWorkController;

            // Create data string for image mining
            $data = "image_mine:{$image->id}:{$image->hash}:{$request->nonce}";
            $pattern = $this->detectHashPattern($request->proof_hash);

            $verification = $proofController->verifyProof(
                $data,
                $request->nonce,
                $request->proof_hash,
                $pattern
            );

            if (! $verification['valid']) {
                return response()->json([
                    'success' => false,
                    'message' => $verification['error'] ?? 'Proof of work verification failed',
                ], 400);
            }

            // Award points based on actual PoW pattern
            $totalPoints = $this->calculateImageMiningPoints($request->proof_hash, $pattern);

            // Create ProofOfWork record
            $userId = session('bitcoin_auth_id');
            \App\Models\ProofOfWork::create([
                'user_id' => $userId,
                'thread_id' => null, // Image mining doesn't relate to threads
                'hash' => $request->proof_hash,
                'nonce' => $request->nonce,
                'data' => $data,
                'pattern' => $pattern,
                'points' => $totalPoints,
                'ip_address' => $request->ip(),
                'verified_at' => now(),
            ]);

            // Award points to user
            if ($userId) {
                $user = \App\Models\BitcoinAuth::find($userId);
                if ($user) {
                    $user->awardMiningPoints($totalPoints);
                }
            }

        } else {
            // Real mining calculation without dummy random values
            $hashRate = $request->hash_rate ?? 1000;

            // Base points calculation based on actual image properties
            $basePoints = max(5, floor($image->file_size / 100000)); // Size-based points

            // File complexity bonus based on hash entropy
            $hashEntropy = $this->calculateHashEntropy($image->hash);
            $entropyBonus = floor($hashEntropy * 3);

            $totalPoints = $basePoints + $entropyBonus;

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

            // Usage-based mining difficulty increase
            if ($image->usage_count > 50) {
                $totalPoints = max(1, floor($totalPoints / 2));
            }
        }

        // Real jackpot calculation based on hash patterns
        $jackpot = $this->calculateJackpot($image->hash, $image->usage_count);
        if ($jackpot > 1) {
            $totalPoints *= $jackpot;
        }

        $image->awardPoW($totalPoints);

        // Award points to user for mouseover mining too
        $userId = session('bitcoin_auth_id');
        if ($userId) {
            $user = \App\Models\BitcoinAuth::find($userId);
            if ($user) {
                $user->awardMiningPoints($totalPoints);
            }
        }

        $message = "Mined {$totalPoints} PoW points!";
        if ($jackpot > 3) {
            $message = "🎰 MEGA JACKPOT! {$totalPoints} PoW points!";
        } elseif (str_starts_with($image->hash, '000')) {
            $message = "💎 LEGENDARY HASH! {$totalPoints} PoW points!";
        } elseif (str_starts_with($image->hash, '666')) {
            $message = "😈 CURSED HASH! {$totalPoints} PoW points!";
        } elseif (str_starts_with($image->hash, 'dead')) {
            $message = "💀 DEATH HASH! {$totalPoints} PoW points!";
        } elseif ($totalPoints > 100) {
            $message = "💰 BIG SCORE! {$totalPoints} PoW points!";
        }

        return response()->json([
            'success' => true,
            'points' => $totalPoints,
            'new_total' => $image->fresh()->total_pow_earned,
            'message' => $message,
            'jackpot' => $jackpot > 1,
            'jackpot_multiplier' => $jackpot,
            'hash_pattern' => substr($image->hash, 0, 8),
            'verified_pow' => $request->has('proof_hash'),
        ]);
    }

    /**
     * Detect hash pattern for PoW verification
     */
    private function detectHashPattern($hash)
    {
        $hash = strtolower($hash);
        if (str_starts_with($hash, '000021e8')) {
            return '000021e8';
        }
        if (str_starts_with($hash, '21e8000')) {
            return '21e8000';
        }
        if (str_starts_with($hash, '21e800')) {
            return '21e800';
        }
        if (str_starts_with($hash, '21e80')) {
            return '21e80';
        }
        if (str_starts_with($hash, '21e8')) {
            return '21e8';
        }
        if (str_starts_with($hash, '000')) {
            return '000';
        } // Special case for legendary
        if (str_starts_with($hash, '666')) {
            return '666';
        } // Special case for cursed
        if (str_starts_with($hash, 'dead')) {
            return 'dead';
        } // Special case for death

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
            '21e800' => 100,  // Updated to match main system
            '21e8000' => 500,  // Scaled up for image mining
            '000021e8' => 3125,
            '000' => 500,  // Legendary
            '666' => 750,  // Cursed
            'dead' => 400,  // Death
        ];

        // Image mining gets bonus multiplier for special engagement
        $points = ($basePoints[$pattern] ?? 1) * 2;

        // Additional bonus for rare image hash patterns
        return $points + rand(5, 50);
    }

    public function upload(Request $request)
    {
        try {
            $request->validate([
                'image' => 'required|file|mimes:jpeg,png,jpg,gif,webp,webm,mp4,mov,avi,svg,bmp,tiff,avif,heic,heif|max:25600', // 25MB max
            ]);

            $image = $request->file('image');

            // Validate file integrity
            if (!$image->isValid()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Upload failed: Invalid file',
                ], 400);
            }

            // Store image and get library entry
            $libraryImage = ImageLibrary::storeImage(
                $image,
                $request->ip(),
                null, // thread_id
                null  // post_id
            );

            // Award real PoW points for uploading based on file properties
            $uploadPoints = $this->calculateUploadPoW($libraryImage);
            $libraryImage->awardPoW($uploadPoints);

            return response()->json([
                'success' => true,
                'image_id' => $libraryImage->id,
                'hash' => $libraryImage->hash,
                'pow_points' => $uploadPoints,
                'file_size' => $libraryImage->file_size,
                'dimensions' => $libraryImage->width && $libraryImage->height ? 
                    "{$libraryImage->width}x{$libraryImage->height}" : 'Unknown',
                'message' => 'Image uploaded successfully!',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed: ' . implode(', ', $e->validator->errors()->all()),
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Image upload failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'ip' => $request->ip(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Upload failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function fullImage($id)
    {
        $image = ImageLibrary::findOrFail($id);

        $path = public_path($image->file_path);

        if (! file_exists($path)) {
            // Try alternative path formats
            $altPath = public_path('storage/' . $image->file_path);
            if (file_exists($altPath)) {
                $path = $altPath;
            } else {
                return response()->json(['error' => 'Image file not found'], 404);
            }
        }

        return response()->file($path, [
            'Content-Type' => $image->mime_type ?? 'image/jpeg',
            'Cache-Control' => 'public, max-age=31536000'
        ]);
    }

    public function download($id)
    {
        $image = ImageLibrary::findOrFail($id);

        $path = public_path($image->file_path);

        if (! file_exists($path)) {
            // Try alternative path formats
            $altPath = public_path('storage/' . $image->file_path);
            if (file_exists($altPath)) {
                $path = $altPath;
            } else {
                return response()->json(['error' => 'Image file not found for download'], 404);
            }
        }

        // Increment usage count for downloads
        $image->markAsUsed(1);

        return response()->download($path, $image->original_name, [
            'Cache-Control' => 'no-cache, must-revalidate'
        ]);
    }

    public function getByHash($hash)
    {
        $image = ImageLibrary::getByHash($hash);

        if (! $image) {
            return response()->json(['error' => 'Image not found'], 404);
        }

        return response()->json([
            'id' => $image->id,
            'filename' => $image->original_name,
            'hash' => $image->hash,
            'total_pow' => $image->total_pow_earned,
            'usage_count' => $image->usage_count,
            'url' => $image->getImageUrl(),
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
            'recent_uploads' => ImageLibrary::getRecent(5),
        ];

        return response()->json($stats);
    }

    public function updateMetadata(Request $request, $id)
    {
        $request->validate([
            'original_name' => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
            'tags' => 'nullable|string|max:255',
        ]);

        $image = ImageLibrary::findOrFail($id);

        // Create metadata array
        $metadata = [
            'description' => $request->description,
            'tags' => $request->tags ? array_map('trim', explode(',', $request->tags)) : [],
            'edited_by_ip' => $request->ip(),
            'edited_at' => now(),
            'original_uploader_ip' => $image->uploader_ip,
        ];

        $image->update([
            'original_name' => $request->original_name,
            'dither_settings' => array_merge($image->dither_settings ?? [], ['metadata' => $metadata]),
        ]);

        // Award real PoW bonus for contributing metadata
        $metadataBonus = $this->calculateMetadataBonus($image, $metadata);
        $image->awardPoW($metadataBonus);

        return response()->json([
            'success' => true,
            'message' => 'Image metadata updated successfully!',
            'image' => [
                'id' => $image->id,
                'original_name' => $image->original_name,
                'description' => $metadata['description'],
                'tags' => $metadata['tags'],
                'total_pow' => $image->fresh()->total_pow_earned,
            ],
        ]);
    }

    public function search(Request $request)
    {
        $request->validate([
            'q' => 'required|string|min:1|max:100',
        ]);

        $images = ImageLibrary::search($request->q, 25);

        return response()->json([
            'query' => $request->q,
            'results' => $images,
            'count' => $images->count(),
        ]);
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
            'shift_factor' => $this->calculateShiftFactor(),
        ]);
    }

    /**
     * Calculate hash entropy for real complexity assessment
     */
    private function calculateHashEntropy($hash)
    {
        $chars = str_split($hash);
        $charCounts = array_count_values($chars);
        $totalChars = strlen($hash);
        $entropy = 0;

        foreach ($charCounts as $count) {
            $probability = $count / $totalChars;
            $entropy -= $probability * log($probability, 2);
        }

        return $entropy;
    }

    /**
     * Calculate real jackpot multiplier based on hash patterns
     */
    private function calculateJackpot($hash, $usageCount)
    {
        $multiplier = 1;

        // Legendary patterns
        if (str_contains($hash, 'deadbeef')) {
            $multiplier = 25;
        } elseif (str_starts_with($hash, '000000')) {
            $multiplier = 15;
        } elseif (str_starts_with($hash, '777')) {
            $multiplier = 12;
        } elseif (str_starts_with($hash, '666')) {
            $multiplier = 8;
        } elseif (str_contains($hash, 'c0de')) {
            $multiplier = 6;
        } elseif (str_contains($hash, '1337')) {
            $multiplier = 5;
        } elseif (str_starts_with($hash, '000')) {
            $multiplier = 4;
        } elseif (preg_match('/^[0-9a-f]{3}\1/', $hash)) {
            $multiplier = 3;
        } // Repeating pattern

        // Reduce jackpot for overused images
        if ($usageCount > 100) {
            $multiplier = max(1, floor($multiplier / 2));
        }

        return $multiplier;
    }

    /**
     * Calculate upload PoW points based on file properties
     */
    private function calculateUploadPoW($image)
    {
        $points = 5; // Base points

        // File size bonus
        if ($image->file_size > 5000000) {
            $points += 10;
        } // 5MB+
        elseif ($image->file_size > 1000000) {
            $points += 5;
        } // 1MB+
        elseif ($image->file_size > 500000) {
            $points += 2;
        } // 500KB+

        // Resolution bonus
        $totalPixels = ($image->width ?? 0) * ($image->height ?? 0);
        if ($totalPixels > 8000000) {
            $points += 8;
        } // 8MP+
        elseif ($totalPixels > 2000000) {
            $points += 4;
        } // 2MP+
        elseif ($totalPixels > 1000000) {
            $points += 2;
        } // 1MP+

        // Hash-based rarity bonus
        $entropy = $this->calculateHashEntropy($image->hash);
        if ($entropy > 3.8) {
            $points += 5;
        } // High entropy
        elseif ($entropy > 3.5) {
            $points += 2;
        } // Medium entropy

        return $points;
    }

    /**
     * Calculate metadata contribution bonus
     */
    private function calculateMetadataBonus($image, $metadata)
    {
        $bonus = 1; // Base bonus

        if (! empty($metadata['description'])) {
            $bonus += strlen($metadata['description']) > 50 ? 3 : 1;
        }

        if (! empty($metadata['tags'])) {
            $bonus += count($metadata['tags']) * 0.5;
        }

        // First-time metadata bonus
        $existingMetadata = $image->dither_settings['metadata'] ?? null;
        if (! $existingMetadata) {
            $bonus *= 2;
        }

        return max(1, floor($bonus));
    }

    /**
     * Calculate dynamic shift factor for library arrangement
     */
    private function calculateShiftFactor()
    {
        // Base shift on current system activity
        $recentUploads = ImageLibrary::where('created_at', '>', now()->subHours(1))->count();
        $activeImages = ImageLibrary::where('updated_at', '>', now()->subMinutes(30))->count();

        return max(1, min(10, $recentUploads + floor($activeImages / 5)));
    }
}
