<?php

namespace App\Http\Controllers;

use App\Models\ImageLibrary;
use Illuminate\Http\Request;

class ImageLibraryController extends Controller
{
    public function index(Request $request)
    {
        try {
            $perPage = 50;
            $sortBy = $request->get('sort', 'newest');
            $currentBoard = $request->get('board', '');
            
            $query = ImageLibrary::query();
            
            // Only add relationships if we actually need them
            if ($currentBoard) {
                $query->with(['firstThread.board']);
                $query->whereHas('firstThread.board', function($q) use ($currentBoard) {
                    $q->where('code', $currentBoard);
                });
            } else {
                // Basic query without complex relationships
                $query->with(['firstThread']);
            }
            
            // Apply sorting
            switch ($sortBy) {
                case 'oldest':
                    $query->orderBy('created_at', 'asc');
                    break;
                case 'most_used':
                    $query->orderBy('created_at', 'desc');
                    break;
                case 'least_used':
                    $query->orderBy('created_at', 'asc');
                    break;
                default:
                    $query->orderBy('created_at', 'desc');
            }
            
            $images = $query->paginate($perPage);
            
            // Transform images safely
            $images->getCollection()->transform(function ($image) {
                try {
                    $boardName = '/general/';
                    $subject = 'Unknown thread';
                    
                    if ($image->firstThread) {
                        $subject = $image->firstThread->title ?? 'Untitled';
                        
                        if ($image->firstThread->board ?? null) {
                            $boardName = '/' . $image->firstThread->board->code . '/';
                        }
                    }
                    
                    return array_merge($image->toArray(), [
                        'board_name' => $boardName,
                        'subject' => $subject,
                        'type' => 'image',
                        'usage_count' => 1,
                        'id' => $image->id,
                        'created_at' => $image->created_at,
                        'file_size' => $image->file_size ?? 0
                    ]);
                } catch (\Exception $e) {
                    \Log::error('Image transform error', ['image_id' => $image->id, 'error' => $e->getMessage()]);
                    
                    // Return safe defaults
                    return array_merge($image->toArray(), [
                        'board_name' => '/general/',
                        'subject' => 'Error loading thread info',
                        'type' => 'image',
                        'usage_count' => 1,
                        'id' => $image->id,
                        'created_at' => $image->created_at,
                        'file_size' => $image->file_size ?? 0
                    ]);
                }
            });
            
            $totalImages = ImageLibrary::count();
            $boards = \App\Models\Board::orderBy('code')->get();
            
            $duplicatesPrevented = 0;
            $total = $images->total();
            
            return view('image-library', compact('images', 'boards', 'sortBy', 'totalImages', 'duplicatesPrevented', 'total', 'currentBoard'));
            
        } catch (\Exception $e) {
            \Log::error('ImageLibrary index error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request' => $request->all()
            ]);
            
            // Return simple fallback view
            return view('image-library', [
                'images' => collect(),
                'boards' => \App\Models\Board::orderBy('code')->get(),
                'sortBy' => 'newest',
                'totalImages' => 0,
                'duplicatesPrevented' => 0,
                'total' => 0,
                'currentBoard' => ''
            ]);
        }
    }
    
    public function shifting()
    {
        try {
            $images = ImageLibrary::orderBy('created_at', 'desc')
                ->limit(50)
                ->get();
                
            $arrangement = $images->map(function($image) {
                return [
                    'id' => $image->id,
                    'hash' => $image->hash,
                    'original_name' => $image->original_name ?? 'Untitled',
                    'usage_count' => 1,
                    'total_pow_earned' => 0
                ];
            });
            
            return response()->json([
                'success' => true,
                'arrangement' => $arrangement
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Image library shifting API error', [
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'error' => 'Failed to load images'
            ], 500);
        }
    }
    
    public function getByHash($hash)
    {
        try {
            $image = ImageLibrary::where('hash', $hash)->first();
            
            if (!$image) {
                return response()->json([
                    'success' => false,
                    'error' => 'Image not found'
                ], 404);
            }
            
            return response()->json([
                'success' => true,
                'image' => [
                    'id' => $image->id,
                    'hash' => $image->hash,
                    'original_name' => $image->original_name,
                    'file_size' => $image->file_size,
                    'mime_type' => $image->mime_type
                ]
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Get image by hash error', [
                'hash' => $hash,
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'error' => 'Failed to get image'
            ], 500);
        }
    }
    
    public function serveImage($id)
    {
        try {
            $image = ImageLibrary::findOrFail($id);
            $imagePath = storage_path('app/images/' . $image->hash);
            
            if (!file_exists($imagePath)) {
                return response()->json(['error' => 'Image file not found'], 404);
            }
            
            return response()->file($imagePath, [
                'Content-Type' => $image->mime_type ?? 'image/jpeg',
                'Cache-Control' => 'public, max-age=3600'
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Serve image error', [
                'id' => $id,
                'error' => $e->getMessage()
            ]);
            
            return response()->json(['error' => 'Failed to serve image'], 500);
        }
    }
}
