#!/bin/bash

echo "=== Fixing Routes File ==="

cd /root/haichan/web-app

# Backup the current routes file
cp routes/web.php routes/web.php.backup

echo "✓ Backed up routes/web.php"

# Find where the routes file got corrupted (look for "public function")
CORRUPTION_LINE=$(grep -n "public function" routes/web.php | head -1 | cut -d: -f1)

if [ -n "$CORRUPTION_LINE" ]; then
    echo "Found corruption at line $CORRUPTION_LINE"
    
    # Extract the clean part before corruption
    head -n $((CORRUPTION_LINE - 1)) routes/web.php > routes/web.php.clean
    
    # Add proper mining routes
    cat >> routes/web.php.clean << 'EOF'

// Haichan Proof of Work Mining Routes
Route::get('/mining', function() {
    return view('mining.dashboard');
})->name('mining.dashboard');

Route::prefix('api')->group(function() {
    Route::post('/submit-proof', [App\Http\Controllers\ProofOfWorkController::class, 'submitProof']);
    Route::get('/mining-stats', [App\Http\Controllers\ProofOfWorkController::class, 'getStats']);
    Route::post('/start-mining-session', [App\Http\Controllers\ProofOfWorkController::class, 'startMiningSession']);
    Route::post('/end-mining-session', [App\Http\Controllers\ProofOfWorkController::class, 'endMiningSession']);
});
EOF
    
    # Replace the corrupted file with the clean one
    mv routes/web.php.clean routes/web.php
    
    echo "✓ Fixed routes/web.php"
else
    echo "No corruption found in routes file"
fi

# Make sure we have the controller
if [ ! -f "app/Http/Controllers/ProofOfWorkController.php" ]; then
    echo "Creating ProofOfWorkController..."
    
    cat > app/Http/Controllers/ProofOfWorkController.php << 'EOF'
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ProofOfWorkController extends Controller
{
    public function submitProof(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'hash' => 'required|string|size:64',
            'nonce' => 'required|integer|min:0',
            'data' => 'required|string',
            'pattern' => 'required|string|in:21e8,21e80,21e800,21e8000,000021e8'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false, 
                'message' => 'Invalid proof format'
            ], 422);
        }

        // Verify the proof
        $hash = $request->input('hash');
        $data = $request->input('data');
        $pattern = $request->input('pattern');
        
        // Calculate hash from data
        $calculatedHash = hash('sha256', $data);
        
        // Verify hash matches
        if ($calculatedHash !== strtolower($hash)) {
            return response()->json([
                'success' => false,
                'message' => 'Hash verification failed'
            ], 400);
        }
        
        // Verify pattern exists in hash
        if (strpos(strtolower($hash), strtolower($pattern)) === false) {
            return response()->json([
                'success' => false,
                'message' => 'Pattern not found in hash'
            ], 400);
        }
        
        // Calculate points
        $points = $this->calculatePoints($pattern);
        
        return response()->json([
            'success' => true,
            'message' => 'Proof accepted!',
            'points' => $points,
            'total_points' => $points // For session tracking
        ]);
    }
    
    private function calculatePoints($pattern)
    {
        $points = [
            '21e8' => 1,
            '21e80' => 5,
            '21e800' => 25,
            '21e8000' => 125,
            '000021e8' => 625
        ];
        
        return $points[$pattern] ?? 1;
    }
    
    public function getStats()
    {
        return response()->json([
            'total_proofs' => 0, // Placeholder for now
            'top_miners' => []   // Placeholder for now
        ]);
    }
    
    public function startMiningSession(Request $request)
    {
        return response()->json([
            'success' => true,
            'message' => 'Mining session started'
        ]);
    }
    
    public function endMiningSession(Request $request)
    {
        return response()->json([
            'success' => true,
            'message' => 'Mining session ended'
        ]);
    }
}
EOF
    
    echo "✓ Created ProofOfWorkController"
fi

# Clear all caches
echo "Clearing caches..."
php artisan config:clear
php artisan route:clear  
php artisan view:clear

# Test the routes
echo "Testing routes..."
php artisan route:list | grep mining

echo
echo "✅ Routes Fixed!"
echo
echo "Now try:"
echo "  php artisan serve"
echo "  http://localhost:8000/mining"
echo