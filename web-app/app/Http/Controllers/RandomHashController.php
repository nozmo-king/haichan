<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Thread;
use App\Models\Post;
use App\Models\ImageLibrary;

class RandomHashController extends Controller
{
    public function getRandomHash()
    {
        // Collect all available hashes from different sources
        $sources = [];
        
        // Get threads with pow_hash
        $threads = Thread::whereNotNull('pow_hash')
            ->whereNotNull('content')
            ->select('id', 'pow_hash', 'title', 'content')
            ->get();
        
        foreach ($threads as $thread) {
            $sources[] = [
                'type' => 'thread',
                'id' => $thread->id,
                'hash' => $thread->pow_hash,
                'preview' => substr($thread->title ?: $thread->content, 0, 50) . '...'
            ];
        }
        
        // Get posts with pow_hash
        $posts = Post::whereNotNull('pow_hash')
            ->whereNotNull('content')
            ->select('id', 'pow_hash', 'content')
            ->get();
        
        foreach ($posts as $post) {
            $sources[] = [
                'type' => 'post',
                'id' => $post->id,
                'hash' => $post->pow_hash,
                'preview' => substr($post->content, 0, 50) . '...'
            ];
        }
        
        // Get images with hash
        $images = ImageLibrary::whereNotNull('hash')
            ->whereNotNull('original_name')
            ->select('id', 'hash', 'original_name')
            ->get();
        
        foreach ($images as $image) {
            $sources[] = [
                'type' => 'image',
                'id' => $image->id,
                'hash' => $image->hash,
                'preview' => $image->original_name
            ];
        }
        
        if (empty($sources)) {
            return response()->json([
                'success' => false,
                'message' => 'No hashes available in database'
            ], 404);
        }
        
        // Select random source
        $randomSource = $sources[array_rand($sources)];
        
        return response()->json([
            'success' => true,
            'hash' => $randomSource['hash'],
            'type' => $randomSource['type'],
            'id' => $randomSource['id'],
            'preview' => $randomSource['preview']
        ]);
    }
}
