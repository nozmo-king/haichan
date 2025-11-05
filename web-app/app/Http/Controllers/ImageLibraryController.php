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
            
            // Return error view or redirect with error
            return redirect()->back()->with('error', 'Unable to load image library: ' . $e->getMessage());
        }
    }
}
