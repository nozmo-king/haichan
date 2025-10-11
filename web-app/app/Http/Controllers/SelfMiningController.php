<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SelfMiningController extends Controller
{
    public function submitPersonal21e8(Request $request)
    {
        if (!session('bitcoin_auth_id')) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
        
        $user = session('bitcoin_auth_user');
        
        // Validate the request
        $request->validate([
            'hash' => 'required|string|size:64|regex:/^[a-f0-9]{64}$/',
            'nonce' => 'required|integer',
            'target' => 'required|string',
            'hashes' => 'required|integer|min:1',
            'time' => 'required|numeric|min:0'
        ]);
        
        // Verify the hash
        $expectedTarget = $user->username . ':' . $user->id . ':' . substr($user->address, 0, 10);
        if ($request->target !== $expectedTarget) {
            return response()->json(['error' => 'Invalid target'], 400);
        }
        
        // Verify the hash is valid
        $data = $expectedTarget . ':' . $request->nonce;
        $computedHash = hash('sha256', $data);
        
        if ($computedHash !== $request->hash) {
            return response()->json(['error' => 'Invalid hash'], 400);
        }
        
        // Check if it's actually a 21e8 (8 leading zeros)
        if (!str_starts_with($request->hash, '00000000')) {
            return response()->json(['error' => 'Not a valid 21e8 hash'], 400);
        }
        
        // Check if user already found their 21e8
        if ($user->personal_21e8_hash) {
            return response()->json(['error' => 'You already found your personal 21e8'], 400);
        }
        
        // Update user record
        $user->update([
            'personal_21e8_hash' => $request->hash,
            'personal_21e8_nonce' => $request->nonce,
            'personal_21e8_total_hashes' => $request->hashes,
            'personal_21e8_mining_time' => $request->time,
            'personal_21e8_found_at' => now(),
            'total_pow_points' => $user->total_pow_points + 1000 // Award 1000 points
        ]);
        
        // Refresh session
        session(['bitcoin_auth_user' => $user->fresh()]);
        
        return response()->json([
            'success' => true,
            'message' => 'Personal 21e8 found and saved!',
            'points_awarded' => 1000
        ]);
    }
    
    public function getLeaderboard()
    {
        $leaders = DB::table('bitcoin_auth')
            ->whereNotNull('personal_21e8_hash')
            ->orderBy('personal_21e8_mining_time', 'asc')
            ->limit(50)
            ->get(['id as user_id', 'username', 'personal_21e8_mining_time as mining_time', 'personal_21e8_total_hashes as total_hashes', 'personal_21e8_found_at as found_at']);
        
        return response()->json([
            'leaders' => $leaders
        ]);
    }
}