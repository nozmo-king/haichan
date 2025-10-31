@extends('layout')

@section('title', 'Image Gallery')

@section('content')
<div class="container">
    <h1>Image Gallery</h1>

    <div class="row">
        <div class="col-md-12">
            <form action="{{ route('image-gallery.index') }}" method="GET">
                <div class="form-group">
                    <label for="sort">Sort by:</label>
                    <select name="sort" id="sort" class="form-control">
                        <option value="pow">PoW</option>
                        <option value="date">Date</option>
                        <option value="size">Size</option>
                        <option value="usage">Usage</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary">Sort</button>
            </form>
        </div>
    </div>

    <div class="row">
        @foreach($images as $image)
            <div class="col-md-4">
                <div class="card">
                    <img src="{{ asset($image->file_path) }}" class="card-img-top" alt="{{ $image->original_name }}">
                    <div class="card-body">
                        <p class="card-text">PoW: {{ $image->total_pow_earned }}</p>
                        <p class="card-text">Date: {{ $image->created_at }}</p>
                        <p class="card-text">Size: {{ $image->file_size }}</p>
                        <p class="card-text">Usage: {{ $image->usage_count }}</p>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>
@endsection