<?php

namespace App\Http\Controllers;

use App\Models\AllowedPublicKey;
use App\Models\BitcoinAuth;
use App\Models\InviteCode;
use App\Models\Post;
use App\Models\Thread;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AdminController extends Controller
{
    public function index()
    {
        // Check if user is admin
        if (! session('bitcoin_auth_user') || ! session('bitcoin_auth_user')->is_admin) {
            return redirect('/')->with('error', 'Admin access required');
        }

        $totalUsers = \App\Models\BitcoinAuth::count();
        $remainingSlots = 256 - $totalUsers;
        $totalProofs = \App\Models\ProofSubmission::count() ?? 0;
        $networkHashrate = \App\Models\ProofSubmission::where('created_at', '>', now()->subMinutes(5))->count() * 12;
        $totalThreads = \App\Models\Thread::count() ?? 0;
        $totalPosts = \App\Models\Post::count() ?? 0;
        $activeInvites = \App\Models\InviteCode::where('uses_remaining', '>', 0)->count();
        $totalUses = \App\Models\InviteCode::where('uses_remaining', '>', 0)->sum('uses_remaining');

        return view('admin.dashboard', compact(
            'totalUsers', 'remainingSlots', 'totalProofs', 'networkHashrate',
            'totalThreads', 'totalPosts', 'activeInvites', 'totalUses'
        ));
    }

    public function keys()
    {
        $allowedKeys = AllowedPublicKey::with('users')->orderBy('created_at', 'desc')->paginate(20);

        return view('admin.keys.index', compact('allowedKeys'));
    }

    public function create()
    {
        return view('admin.keys.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'public_key' => 'required|string|size:66|unique:allowed_public_keys,public_key',
            'label' => 'nullable|string|max:255',
            'is_active' => 'boolean',
        ]);

        // Validate public key format (compressed secp256k1)
        if (! preg_match('/^(02|03)[a-f0-9]{64}$/i', $request->public_key)) {
            return back()->withErrors(['public_key' => 'Invalid secp256k1 public key format']);
        }

        AllowedPublicKey::create([
            'public_key' => strtolower($request->public_key),
            'label' => $request->label,
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()->route('admin.keys.index')->with('success', 'Public key added successfully');
    }

    public function edit(AllowedPublicKey $allowedKey)
    {
        return view('admin.keys.edit', compact('allowedKey'));
    }

    public function update(Request $request, AllowedPublicKey $allowedKey)
    {
        $request->validate([
            'label' => 'nullable|string|max:255',
            'is_active' => 'boolean',
        ]);

        $allowedKey->update([
            'label' => $request->label,
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()->route('admin.keys.index')->with('success', 'Public key updated successfully');
    }

    public function destroy(AllowedPublicKey $allowedKey)
    {
        $allowedKey->delete();

        return redirect()->route('admin.keys.index')->with('success', 'Public key removed successfully');
    }

    // User Management
    public function users(Request $request)
    {
        if (! session('bitcoin_auth_user') || ! session('bitcoin_auth_user')->is_admin) {
            return redirect('/')->with('error', 'Admin access required');
        }

        $query = BitcoinAuth::query();

        switch ($request->get('filter')) {
            case 'admins':
                $query->where('admin_level', '>', 0);
                break;
            case 'banned':
                $query->where('is_banned', true);
                break;
            case 'recent':
                $query->where('created_at', '>', now()->subDays(7));
                break;
        }

        $users = $query->orderBy('created_at', 'desc')->get();

        return view('admin.users', compact('users'));
    }

    public function banUser($id)
    {
        $user = BitcoinAuth::findOrFail($id);
        $user->update(['is_banned' => true, 'banned_until' => null, 'ban_reason' => 'Banned by admin']);

        return back()->with('success', 'User banned successfully');
    }

    public function unbanUser($id)
    {
        $user = BitcoinAuth::findOrFail($id);
        $user->update(['is_banned' => false, 'banned_until' => null, 'ban_reason' => null]);

        return back()->with('success', 'User unbanned successfully');
    }

    public function promoteUser($id)
    {
        $user = BitcoinAuth::findOrFail($id);
        $newLevel = min(5, $user->admin_level + 1);
        $user->update(['admin_level' => $newLevel, 'is_admin' => $newLevel > 0]);

        return back()->with('success', 'User promoted successfully');
    }

    public function demoteUser($id)
    {
        $user = BitcoinAuth::findOrFail($id);
        $newLevel = max(0, $user->admin_level - 1);
        $user->update(['admin_level' => $newLevel, 'is_admin' => $newLevel > 0]);

        return back()->with('success', 'User demoted successfully');
    }

    // Forum Moderation
    public function forum(Request $request)
    {
        if (! session('bitcoin_auth_user') || ! session('bitcoin_auth_user')->is_admin) {
            return redirect('/')->with('error', 'Admin access required');
        }

        $stats = [
            'threads' => Thread::count(),
            'posts' => Post::count(),
            'pinned' => Thread::where('sticky', true)->count(),
            'locked' => Thread::where('locked', true)->count(),
        ];

        $threads = Thread::with(['board', 'bitcoinUser'])
            ->orderBy('created_at', 'desc')
            ->limit(50)
            ->get();

        $posts = Post::with(['thread.board', 'bitcoinUser'])
            ->orderBy('created_at', 'desc')
            ->limit(50)
            ->get();

        return view('admin.forum', compact('stats', 'threads', 'posts'));
    }

    public function pinThread($id)
    {
        $thread = Thread::findOrFail($id);
        $thread->update(['sticky' => true]);

        return back()->with('success', 'Thread pinned successfully');
    }

    public function lockThread($id)
    {
        $thread = Thread::findOrFail($id);
        $thread->update(['locked' => true]);

        return back()->with('success', 'Thread locked successfully');
    }

    public function deleteThread($id)
    {
        $thread = Thread::findOrFail($id);
        // Also delete related posts
        Post::where('thread_id', $id)->delete();
        $thread->delete();

        return back()->with('success', 'Thread deleted successfully');
    }

    public function deletePost($id)
    {
        $post = Post::findOrFail($id);
        $post->delete();

        return back()->with('success', 'Post deleted successfully');
    }

    // Genesis Code Management
    public function createGenesisCode(Request $request)
    {
        $request->validate([
            'code' => 'required|string|max:32|unique:invite_codes,code',
            'uses' => 'required|integer|min:1|max:50',
        ]);

        InviteCode::create([
            'code' => strtoupper($request->code),
            'max_uses' => $request->uses,
            'uses_remaining' => $request->uses,
            'created_by' => session('bitcoin_auth_id'),
        ]);

        return back()->with('success', 'Genesis code created successfully');
    }

    // API endpoint for activity feed
    public function getActivity()
    {
        $activities = [];

        // Recent registrations
        $recentUsers = BitcoinAuth::where('created_at', '>', now()->subHours(24))
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        foreach ($recentUsers as $user) {
            $activities[] = [
                'description' => "New user registered: {$user->username}",
                'time' => $user->created_at->diffForHumans(),
            ];
        }

        // Recent threads
        $recentThreads = Thread::where('created_at', '>', now()->subHours(24))
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        foreach ($recentThreads as $thread) {
            $activities[] = [
                'description' => 'New thread: '.Str::limit($thread->title, 40),
                'time' => $thread->created_at->diffForHumans(),
            ];
        }

        // Sort by time
        usort($activities, function ($a, $b) {
            return strcmp($b['time'], $a['time']);
        });

        return response()->json(array_slice($activities, 0, 10));
    }

    // API endpoint for invite codes modal
    public function getInviteCodes()
    {
        if (! session('bitcoin_auth_user') || ! session('bitcoin_auth_user')->is_admin) {
            return response()->json(['error' => 'Admin access required'], 403);
        }

        $codes = InviteCode::orderBy('created_at', 'desc')->get();

        return response()->json($codes);
    }

    public function deactivateInviteCode($code)
    {
        if (! session('bitcoin_auth_user') || ! session('bitcoin_auth_user')->is_admin) {
            return response()->json(['error' => 'Admin access required'], 403);
        }

        $inviteCode = InviteCode::where('code', strtoupper($code))->first();

        if (! $inviteCode) {
            return response()->json(['error' => 'Code not found'], 404);
        }

        $inviteCode->update(['uses_remaining' => 0]);

        return response()->json(['success' => 'Code deactivated successfully']);
    }

    public function deleteInviteCode($code)
    {
        if (! session('bitcoin_auth_user') || ! session('bitcoin_auth_user')->is_admin) {
            return response()->json(['error' => 'Admin access required'], 403);
        }

        $inviteCode = InviteCode::where('code', strtoupper($code))->first();

        if (! $inviteCode) {
            return response()->json(['error' => 'Code not found'], 404);
        }

        $inviteCode->delete();

        return response()->json(['success' => 'Code deleted successfully']);
    }
}
