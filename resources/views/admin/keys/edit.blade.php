@extends('layout')

@section('title', 'Edit Public Key')

@section('content')
<div class="max-w-2xl mx-auto p-6">
    <div class="mb-6">
        <h1 class="text-2xl font-bold">Edit Public Key</h1>
        <p class="text-gray-600 mt-2 font-mono text-sm break-all">{{ $allowedKey->public_key }}</p>
    </div>

    <form action="{{ route('admin.keys.update', $allowedKey) }}" method="POST" class="space-y-6">
        @csrf
        @method('PUT')

        <div>
            <label for="label" class="block text-sm font-medium text-gray-700">
                Label
            </label>
            <input type="text" 
                   name="label" 
                   id="label"
                   value="{{ old('label', $allowedKey->label) }}"
                   placeholder="User description (optional)"
                   class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 @error('label') border-red-500 @enderror">
            @error('label')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div class="flex items-center">
            <input type="checkbox" 
                   name="is_active" 
                   id="is_active" 
                   value="1"
                   {{ old('is_active', $allowedKey->is_active) ? 'checked' : '' }}
                   class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
            <label for="is_active" class="ml-2 block text-sm text-gray-900">
                Active (allow authentication)
            </label>
        </div>

        @if($allowedKey->users->count() > 0)
            <div class="bg-yellow-50 border border-yellow-200 rounded-md p-4">
                <h3 class="text-sm font-medium text-yellow-800">Warning</h3>
                <p class="mt-1 text-sm text-yellow-700">
                    This public key has {{ $allowedKey->users->count() }} associated user(s). 
                    Deactivating it will prevent them from logging in.
                </p>
            </div>
        @endif

        <div class="flex space-x-4">
            <button type="submit" class="bg-blue-500 text-white px-6 py-2 rounded hover:bg-blue-600">
                Update Public Key
            </button>
            <a href="{{ route('admin.keys.index') }}" class="bg-gray-300 text-gray-700 px-6 py-2 rounded hover:bg-gray-400">
                Cancel
            </a>
        </div>
    </form>
</div>
@endsection