<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Models\Board;
use App\Models\Thread;
use App\Models\Post;

class BoardController extends Controller
{
    public function index()
    {
        $boards = Board::where('active', true)
            ->orderBy('name')
            ->withCount('threads')
            ->get();

        return view('boards.index', compact('boards'));
    }

    public function show(Request $request, $boardName)
    {
        $board = Board::where('name', $boardName)->where('active', true)->firstOrFail();
        
        $threads = $board->threads()
            ->with(['posts' => function($query) {
                $query->latest()->limit(3);
            }])
            ->orderBy('sticky', 'desc')
            ->orderBy('bumped_at', 'desc')
            ->paginate(20);

        return view('boards.show', compact('board', 'threads'));
    }

    public function showThread(Request $request, $boardName, $threadId)
    {
        $board = Board::where('name', $boardName)->where('active', true)->firstOrFail();
        $thread = Thread::where('board_id', $board->id)->findOrFail($threadId);
        
        $posts = $thread->posts()
            ->orderBy('created_at', 'asc')
            ->get();

        return view('boards.thread', compact('board', 'thread', 'posts'));
    }

    public function storeThread(Request $request, $boardName)
    {
        $board = Board::where('name', $boardName)->where('active', true)->firstOrFail();
        
        $request->validate([
            'subject' => 'nullable|string|max:200',
            'content' => 'required|string|max:8000',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,gif,webp|max:10240'
        ]);

        $imageData = null;
        if ($request->hasFile('image')) {
            $imageData = $this->handleImageUpload($request->file('image'));
        }

        $posterHash = Thread::generatePosterHash($request->ip(), 0);

        $thread = Thread::create([
            'board_id' => $board->id,
            'subject' => $request->subject,
            'content' => $request->content,
            'image_filename' => $imageData['filename'] ?? null,
            'image_original_name' => $imageData['original_name'] ?? null,
            'image_size' => $imageData['size'] ?? null,
            'ip_address' => $request->ip(),
            'poster_hash' => $posterHash,
            'bumped_at' => now(),
            'bump_score' => 0,
            'reply_count' => 0,
            'image_count' => $imageData ? 1 : 0
        ]);

        // Update poster hash with actual thread ID
        $thread->update([
            'poster_hash' => Thread::generatePosterHash($request->ip(), $thread->id)
        ]);

        $board->incrementPostCount();

        return redirect("/{$board->name}/thread/{$thread->id}");
    }

    public function storePost(Request $request, $boardName, $threadId)
    {
        $board = Board::where('name', $boardName)->where('active', true)->firstOrFail();
        $thread = Thread::where('board_id', $board->id)->findOrFail($threadId);
        
        if ($thread->locked) {
            return back()->withErrors(['error' => 'Thread is locked']);
        }

        $request->validate([
            'content' => 'required|string|max:8000',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,gif,webp|max:10240'
        ]);

        $imageData = null;
        if ($request->hasFile('image')) {
            $imageData = $this->handleImageUpload($request->file('image'));
        }

        $posterHash = Thread::generatePosterHash($request->ip(), $thread->id);

        $post = Post::create([
            'thread_id' => $thread->id,
            'content' => $request->content,
            'image_filename' => $imageData['filename'] ?? null,
            'image_original_name' => $imageData['original_name'] ?? null,
            'image_size' => $imageData['size'] ?? null,
            'ip_address' => $request->ip(),
            'poster_hash' => $posterHash
        ]);

        $thread->addReply($post);

        return redirect("/{$board->name}/thread/{$thread->id}#post{$post->id}");
    }

    private function handleImageUpload($file)
    {
        $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();
        $originalName = $file->getClientOriginalName();
        $size = $file->getSize();

        // Store original image using the public disk
        $file->storeAs('images', $filename, 'public');

        // Create simple thumbnail (copy for now)
        $file->storeAs('thumbs', $filename, 'public');

        return [
            'filename' => $filename,
            'original_name' => $originalName,
            'size' => $size
        ];
    }
}
