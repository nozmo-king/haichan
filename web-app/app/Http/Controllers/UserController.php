<?php

namespace App\Http\Controllers;

use App\Models\BitcoinAuth;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function showDashboard()
    {
        $user = session('bitcoin_auth_user');
        if (!$user) {
            return redirect('/login');
        }
        
        // Get user stats
        $stats = [
            'posts' => \DB::table('posts')->where('user_id', $user->id)->count(),
            'threads' => \DB::table('threads')->where('user_id', $user->id)->count(),
            'pow_points' => $user->total_pow_points ?? 0,
            'level' => $user->level ?? 1,
        ];
        
        return view('user.dashboard', compact('user', 'stats'));
    }
    
    public function showEditProfile()
    {
        $user = session('bitcoin_auth_user');
        if (!$user) {
            return redirect('/login');
        }
        
        return view('user.profile-edit', compact('user'));
    }
    
    public function updateProfile(Request $request)
    {
        $user = session('bitcoin_auth_user');
        if (!$user) {
            return redirect('/login');
        }
        
        $request->validate([
            'display_name' => 'nullable|string|max:50',
            'bio' => 'nullable|string|max:500',
            'location' => 'nullable|string|max:100',
            'website' => 'nullable|url|max:200',
        ]);
        
        // Update user
        $userModel = BitcoinAuth::find($user->id);
        $userModel->update([
            'display_name' => $request->display_name,
            'bio' => $request->bio,
            'location' => $request->location,
            'website' => $request->website,
        ]);
        
        // Refresh session
        session(['bitcoin_auth_user' => $userModel->fresh()]);
        
        return redirect()->back()->with('success', 'Profile updated successfully!');
    }
    
    public function showUserProfile($userId)
    {
        $user = BitcoinAuth::findOrFail($userId);
        
        // Get user stats
        $stats = [
            'posts' => \DB::table('posts')->where('user_id', $user->id)->count(),
            'threads' => \DB::table('threads')->where('user_id', $user->id)->count(),
            'pow_points' => $user->total_pow_points ?? 0,
            'level' => $user->level ?? 1,
        ];
        
        // Get attestations (empty collection for now)
        $attestations = collect([]);
        
        return view('user.profile', compact('user', 'stats', 'attestations'));
    }
}