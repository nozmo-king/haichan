@extends('layout')

@section('title', 'Manage Allowed Public Keys')

@section('content')
<div class="max-w-6xl mx-auto p-6">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">Allowed Public Keys</h1>
        <a href="{{ route('admin.keys.create') }}" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
            Add New Key
        </a>
    </div>

    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            {{ session('success') }}
        </div>
    @endif

    <div class="bg-white shadow overflow-hidden sm:rounded-md">
        <ul class="divide-y divide-gray-200">
            @forelse($allowedKeys as $key)
                <li class="px-6 py-4">
                    <div class="flex items-center justify-between">
                        <div class="flex-1">
                            <div class="flex items-center justify-between">
                                <p class="text-sm font-medium text-gray-900 truncate">
                                    {{ $key->label ?: 'Unlabeled Key' }}
                                </p>
                                <div class="ml-2 flex-shrink-0 flex">
                                    @if($key->is_active)
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                            Active
                                        </span>
                                    @else
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                            Inactive
                                        </span>
                                    @endif
                                </div>
                            </div>
                            <p class="mt-2 text-sm text-gray-500 font-mono break-all">
                                {{ $key->public_key }}
                            </p>
                            <p class="mt-1 text-xs text-gray-400">
                                Added {{ $key->created_at->diffForHumans() }} • {{ $key->users->count() }} user(s)
                            </p>
                        </div>
                        <div class="ml-4 flex space-x-2">
                            <a href="{{ route('admin.keys.edit', $key) }}" class="text-blue-600 hover:text-blue-900">
                                Edit
                            </a>
                            <form action="{{ route('admin.keys.destroy', $key) }}" method="POST" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600 hover:text-red-900" 
                                        onclick="return confirm('Are you sure? This will remove all associated users.')">
                                    Delete
                                </button>
                            </form>
                        </div>
                    </div>
                </li>
            @empty
                <li class="px-6 py-4 text-center text-gray-500">
                    No public keys configured. <a href="{{ route('admin.keys.create') }}" class="text-blue-600">Add the first one</a>.
                </li>
            @endforelse
        </ul>
    </div>

    @if($allowedKeys->hasPages())
        <div class="mt-6">
            {{ $allowedKeys->links() }}
        </div>
    @endif
</div>
@endsection