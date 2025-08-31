@extends('layout')

@section('title', 'Create Thread - ' . $board->code)

@section('content')
<div class="breadcrumb">
    <a href="{{ route('forum.index') }}">Forum</a> > 
    <a href="{{ route('forum.board', $board->code) }}">{{ $board->code }}</a> > 
    Create Thread
</div>

<h2>Create Thread in /{{ $board->code }}/</h2>

<form action="{{ route('forum.store', $board->code) }}" method="POST" enctype="multipart/form-data">
    @csrf
    
    <div class="form-group">
        <label for="title">Title:</label>
        <input type="text" name="title" id="title" required maxlength="255" value="{{ old('title') }}" 
               style="width: 100%; padding: 5px; margin: 5px 0;">
        @error('title')
            <div style="color: red; font-size: 12px;">{{ $message }}</div>
        @enderror
    </div>
    
    <div class="form-group">
        <label for="content">Content:</label>
        <textarea name="content" id="content" rows="8" required maxlength="2000" 
                  style="width: 100%; padding: 5px; margin: 5px 0;">{{ old('content') }}</textarea>
        @error('content')
            <div style="color: red; font-size: 12px;">{{ $message }}</div>
        @enderror
    </div>
    
    <div class="form-group">
        <label for="image">Image (optional):</label>
        <input type="file" name="image" id="image" accept="image/*" 
               style="width: 100%; padding: 5px; margin: 5px 0;">
        <small style="color: #666; font-size: 12px;">Max size: 2MB. Supported formats: JPEG, PNG, JPG, GIF</small>
        @error('image')
            <div style="color: red; font-size: 12px;">{{ $message }}</div>
        @enderror
    </div>
    
    
    <div class="form-group">
        <button type="submit" style="padding: 8px 16px; margin: 10px 0; background: #007bff; color: white; border: none; border-radius: 4px;">Create Thread</button>
        <a href="{{ route('forum.board', $board->code) }}" style="margin-left: 10px;">Cancel</a>
    </div>
</form>

</script>
@endsection