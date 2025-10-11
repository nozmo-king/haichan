<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Models\BitcoinAuth;
use Intervention\Image\Facades\Image;

class UserProfileController extends Controller
{
    /**
     * Show user profile page
     */
    public function show()
    {
        $user = $this->getBitcoinAuthUser();
        
        if (!$user) {
            return redirect('/auth/login')->withErrors(['auth' => 'Please log in to view profile']);
        }
        
        return view('profile.show', compact('user'));
    }

    /**
     * Upload user favicon/avatar
     */
    public function uploadFavicon(Request $request)
    {
        $user = $this->getBitcoinAuthUser();
        
        if (!$user) {
            return response()->json(['success' => false, 'error' => 'Authentication required'], 401);
        }
        
        $request->validate([
            'favicon' => 'required|image|mimes:png,jpg,jpeg,gif,webp|max:1024', // Max 1MB
        ]);
        
        try {
            $file = $request->file('favicon');
            
            // Generate unique filename
            $filename = 'favicon_' . $user->id . '_' . Str::random(8) . '.png';
            
            // Process image - resize to 32x32 for favicon use
            $image = Image::make($file)
                ->resize(32, 32)
                ->encode('png', 90);
            
            // Store in public disk
            Storage::disk('public')->put('avatars/' . $filename, $image);
            
            // Calculate hash for the avatar
            $avatarHash = hash('sha256', $image);
            
            // Update user's avatar_hash
            $user->update([
                'avatar_hash' => $avatarHash,
                'avatar_filename' => $filename
            ]);
            
            return response()->json([
                'success' => true,
                'avatar_url' => Storage::disk('public')->url('avatars/' . $filename),
                'message' => 'Favicon uploaded successfully!'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Failed to upload favicon: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get user avatar URL
     */
    public function getAvatarUrl(BitcoinAuth $user): string
    {
        if ($user->avatar_filename && Storage::disk('public')->exists('avatars/' . $user->avatar_filename)) {
            return Storage::disk('public')->url('avatars/' . $user->avatar_filename);
        }
        
        // Generate default identicon based on user address
        return '/default-avatar/' . substr(hash('sha256', $user->address), 0, 8) . '.png';
    }

    /**
     * Generate default identicon avatar
     */
    public function generateIdenticon($hash)
    {
        // Simple identicon generation based on hash
        $size = 32;
        $image = imagecreate($size, $size);
        
        // Background color
        $bg = imagecolorallocate($image, 240, 240, 240);
        
        // Generate pattern color from hash
        $r = hexdec(substr($hash, 0, 2));
        $g = hexdec(substr($hash, 2, 2));
        $b = hexdec(substr($hash, 4, 2));
        $color = imagecolorallocate($image, $r, $g, $b);
        
        // Create simple pattern based on hash
        for ($y = 0; $y < $size; $y++) {
            for ($x = 0; $x < $size; $x++) {
                $index = ($y * $size + $x) % strlen($hash);
                if (hexdec($hash[$index]) % 2 === 0) {
                    imagesetpixel($image, $x, $y, $color);
                }
            }
        }
        
        header('Content-Type: image/png');
        imagepng($image);
        imagedestroy($image);
    }

    /**
     * Get the current authenticated BitcoinAuth user
     */
    private function getBitcoinAuthUser()
    {
        $userId = session('bitcoin_auth_id');
        
        if (!$userId || !is_numeric($userId)) {
            return null;
        }
        
        return BitcoinAuth::find($userId);
    }
}