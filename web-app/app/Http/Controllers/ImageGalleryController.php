<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ImageGalleryController extends Controller
{
    public function index(Request $request)
    {
        $sort = $request->get('sort', 'pow');

        switch ($sort) {
            case 'date':
                $images = \App\Models\ImageGallery::orderBy('created_at', 'desc')->get();
                break;
            case 'size':
                $images = \App\Models\ImageGallery::orderBy('file_size', 'desc')->get();
                break;
            case 'usage':
                $images = \App\Models\ImageGallery::orderBy('usage_count', 'desc')->get();
                break;
            default:
                $images = \App\Models\ImageGallery::orderBy('total_pow_earned', 'desc')->get();
                break;
        }

        return view('image-gallery.index', compact('images'));
    }
}
