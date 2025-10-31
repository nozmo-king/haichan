<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Thread;
use Illuminate\Http\Request;

class ImageLibraryController extends Controller
{
    public function index(Request $request)
    {
        $perPage = 50;
        $board = $request->get('board');
        $sortBy = $request->get('sort', 'newest'); // newest, oldest, most_used, least_used
        
        // Get images from ImageGallery table
        $query = \App\Models\ImageGallery::query();
        
        // Apply board filtering if specified
        if ($board) {
            $query->where('board_code', $board);
        }
        
        // Apply sorting to encourage reuse and show duplicate prevention
        switch ($sortBy) {
            case 'oldest':
                $query->orderBy('created_at', 'asc');
                break;
            case 'most_used':
                $query->orderBy('usage_count', 'desc')->orderBy('created_at', 'desc');
                break;
            case 'least_used':
                $query->orderBy('usage_count', 'asc')->orderBy('created_at', 'desc');
                break;
            case 'newest':
            default:
                $query->orderBy('created_at', 'desc');
                break;
        }
        
        $images = $query->paginate($perPage, ['*'], 'page', $request->get('page', 1))
            ->through(function($image) {
                return [
                    'type' => 'gallery',
                    'id' => $image->id,
                    'image_path' => $image->file_path,
                    'board_code' => $image->board_code ?? 'general',
                    'board_name' => $image->board_name ?? 'General',
                    'subject' => $image->original_name,
                    'created_at' => $image->created_at,
                    'url' => '#', // Gallery images don't have specific URLs
                    'usage_count' => $image->usage_count,
                    'hash' => $image->hash,
                    'file_size' => $image->file_size ?? 0,
                ];
            });
        
        // Get statistics for duplicate prevention info
        $totalImages = \App\Models\ImageGallery::count();
        $totalReuses = \App\Models\ImageGallery::sum('usage_count') - $totalImages;
        $duplicatesPrevented = $totalReuses;
        
        // Get all boards for filter
        $boards = \App\Models\Board::orderBy('code')->get();
        
        return view('image-library', [
            'images' => $images->items(),
            'boards' => $boards,
            'currentBoard' => $board,
            'sortBy' => $sortBy,
            'total' => $images->total(),
            'currentPage' => $images->currentPage(),
            'lastPage' => $images->lastPage(),
            'perPage' => $perPage,
            'totalImages' => $totalImages,
            'duplicatesPrevented' => $duplicatesPrevented,
        ]);
    }
}
