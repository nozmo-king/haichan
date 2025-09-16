@extends('layout')

@section('title', 'Add Public Key')

@section('content')
<div class="max-w-2xl mx-auto p-6">
    <div class="mb-6">
        <h1 class="text-2xl font-bold">Add New Public Key</h1>
        <p class="text-gray-600 mt-2">Add a secp256k1 public key to the allowlist.</p>
    </div>

    <form action="{{ route('admin.keys.store') }}" method="POST" class="space-y-6">
        @csrf

        <div>
            <label for="public_key" class="block text-sm font-medium text-gray-700">
                Public Key <span class="text-red-500">*</span>
            </label>
            <input type="text" 
                   name="public_key" 
                   id="public_key"
                   value="{{ old('public_key') }}"
                   placeholder="02a1b2c3d4e5f6..."
                   class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 font-mono text-sm @error('public_key') border-red-500 @enderror">
            @error('public_key')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
            <p class="mt-1 text-xs text-gray-500">
                Compressed secp256k1 public key (66 hex characters starting with 02 or 03)
            </p>
        </div>

        <div>
            <label for="label" class="block text-sm font-medium text-gray-700">
                Label
            </label>
            <input type="text" 
                   name="label" 
                   id="label"
                   value="{{ old('label') }}"
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
                   {{ old('is_active', true) ? 'checked' : '' }}
                   class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
            <label for="is_active" class="ml-2 block text-sm text-gray-900">
                Active (allow authentication)
            </label>
        </div>

        <div class="flex space-x-4">
            <button type="submit" class="bg-blue-500 text-white px-6 py-2 rounded hover:bg-blue-600">
                Add Public Key
            </button>
            <a href="{{ route('admin.keys.index') }}" class="bg-gray-300 text-gray-700 px-6 py-2 rounded hover:bg-gray-400">
                Cancel
            </a>
        </div>
    </form>
</div>
@endsection