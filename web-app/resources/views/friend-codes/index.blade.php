@extends('layout')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900 mb-2">Friend Code Management</h1>
        <p class="text-gray-600">Manage your friend codes and track referrals</p>
    </div>

    @if (session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6">
            {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
            {{ session('error') }}
        </div>
    @endif

    <!-- Stats Cards -->
    <div class="grid md:grid-cols-4 gap-6 mb-8">
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-2 bg-blue-100 rounded-lg">
                    <svg class="w-6 h-6 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <h3 class="text-lg font-semibold text-gray-900">{{ $stats['total'] }}</h3>
                    <p class="text-sm text-gray-600">Total Codes</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-2 bg-green-100 rounded-lg">
                    <svg class="w-6 h-6 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <h3 class="text-lg font-semibold text-gray-900">{{ $stats['active'] }}</h3>
                    <p class="text-sm text-gray-600">Active Codes</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-2 bg-purple-100 rounded-lg">
                    <svg class="w-6 h-6 text-purple-600" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M13 6a3 3 0 11-6 0 3 3 0 016 0zM18 8a2 2 0 11-4 0 2 2 0 014 0zM14 15a4 4 0 00-8 0v3h8v-3z"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <h3 class="text-lg font-semibold text-gray-900">{{ $stats['used'] }}</h3>
                    <p class="text-sm text-gray-600">Used Codes</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-2 bg-yellow-100 rounded-lg">
                    <svg class="w-6 h-6 text-yellow-600" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z" clip-rule="evenodd"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <h3 class="text-lg font-semibold text-gray-900">{{ $stats['referrals'] }}</h3>
                    <p class="text-sm text-gray-600">Successful Referrals</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Generate New Code -->
    <div class="bg-white rounded-lg shadow p-6 mb-8">
        <h2 class="text-xl font-bold text-gray-900 mb-4">Generate Friend Code</h2>
        
        @php
            $activeFriendCode = $friendCodes->firstWhere(function($code) {
                return !$code->is_used && ($code->expires_at === null || $code->expires_at->isFuture());
            });
        @endphp

        @if ($activeFriendCode)
            <div class="bg-green-50 border border-green-200 rounded p-4 mb-4">
                <h3 class="font-medium text-green-800 mb-2">Your Active Friend Code:</h3>
                <div class="flex items-center space-x-4">
                    <code class="bg-white px-3 py-2 rounded border text-lg font-mono">{{ $activeFriendCode->code }}</code>
                    <button onclick="copyCode('{{ $activeFriendCode->code }}')" class="bg-green-600 text-white px-3 py-1 rounded text-sm hover:bg-green-700">
                        Copy
                    </button>
                    <button onclick="shareCode('{{ $activeFriendCode->code }}')" class="bg-blue-600 text-white px-3 py-1 rounded text-sm hover:bg-blue-700">
                        Share
                    </button>
                </div>
                @if ($activeFriendCode->expires_at)
                    <p class="text-sm text-green-600 mt-2">
                        Expires: {{ $activeFriendCode->expires_at->format('M j, Y g:i A') }}
                    </p>
                @endif
            </div>
            
            <div class="bg-gray-50 rounded p-4">
                <h4 class="font-medium text-gray-800 mb-2">Share this registration link:</h4>
                <div class="flex items-center space-x-2">
                    <input type="text" 
                           id="registrationLink" 
                           value="{{ route('auth.register', $activeFriendCode->code) }}" 
                           readonly 
                           class="flex-1 px-3 py-2 bg-white border rounded text-sm">
                    <button onclick="copyLink()" class="bg-indigo-600 text-white px-3 py-1 rounded text-sm hover:bg-indigo-700">
                        Copy Link
                    </button>
                </div>
            </div>
        @else
            <p class="text-gray-600 mb-4">Friend code generation is currently managed through the admin panel.</p>
            <p class="text-sm text-gray-500">Contact an administrator to request a friend code.</p>
            {{-- Friend code generation form disabled until route is implemented
            <form action="{{ route('friend-codes.generate') }}" method="POST">
                @csrf
                <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700">
                    Generate Friend Code
                </button>
            </form>
            --}}
        @endif
    </div>

    <!-- Friend Codes List -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-xl font-bold text-gray-900">Your Friend Codes</h2>
        </div>

        @if ($friendCodes->count() > 0)
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Code</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Used By</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Expires</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Created</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach ($friendCodes as $friendCode)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <code class="text-sm font-mono bg-gray-100 px-2 py-1 rounded">
                                        {{ substr($friendCode->code, 0, 8) }}...{{ substr($friendCode->code, -8) }}
                                    </code>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if ($friendCode->is_used)
                                        <span class="inline-flex px-2 py-1 text-xs font-medium rounded-full bg-gray-100 text-gray-800">
                                            Used
                                        </span>
                                    @elseif ($friendCode->expires_at && $friendCode->expires_at->isPast())
                                        <span class="inline-flex px-2 py-1 text-xs font-medium rounded-full bg-red-100 text-red-800">
                                            Expired
                                        </span>
                                    @else
                                        <span class="inline-flex px-2 py-1 text-xs font-medium rounded-full bg-green-100 text-green-800">
                                            Active
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    @if ($friendCode->usedByUser)
                                        <span class="text-green-600">User #{{ $friendCode->usedByUser->id }}</span>
                                    @else
                                        <span class="text-gray-400">-</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    @if ($friendCode->expires_at)
                                        {{ $friendCode->expires_at->format('M j, Y g:i A') }}
                                    @else
                                        Never
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $friendCode->created_at->format('M j, Y g:i A') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    @if (!$friendCode->is_used && $friendCode->isValid())
                                        <button onclick="copyCode('{{ $friendCode->code }}')" class="text-indigo-600 hover:text-indigo-900 mr-3">
                                            Copy
                                        </button>
                                        <button onclick="shareCode('{{ $friendCode->code }}')" class="text-blue-600 hover:text-blue-900">
                                            Share
                                        </button>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            <div class="px-6 py-4 border-t border-gray-200">
                {{ $friendCodes->links() }}
            </div>
        @else
            <div class="px-6 py-8 text-center">
                <p class="text-gray-500">No friend codes found.</p>
            </div>
        @endif
    </div>
</div>

<script nonce="{{ app('csp_nonce') }}">
function copyCode(code) {
    navigator.clipboard.writeText(code).then(function() {
        showNotification('Friend code copied to clipboard!', 'success');
    }, function(err) {
        showNotification('Failed to copy code', 'error');
    });
}

function copyLink() {
    const link = document.getElementById('registrationLink').value;
    navigator.clipboard.writeText(link).then(function() {
        showNotification('Registration link copied to clipboard!', 'success');
    }, function(err) {
        showNotification('Failed to copy link', 'error');
    });
}

function shareCode(code) {
    const url = `{{ url('/register') }}/${code}`;
    
    if (navigator.share) {
        navigator.share({
            title: 'Join our exclusive forum',
            text: 'You\'ve been invited to join our exclusive forum community!',
            url: url
        });
    } else {
        copyCode(code);
        showNotification('Friend code copied! Share this link: ' + url, 'info');
    }
}

function showNotification(message, type) {
    // Create notification element
    const notification = document.createElement('div');
    notification.className = `fixed top-4 right-4 px-4 py-3 rounded shadow-lg z-50 ${
        type === 'success' ? 'bg-green-100 border border-green-400 text-green-700' :
        type === 'error' ? 'bg-red-100 border border-red-400 text-red-700' :
        'bg-blue-100 border border-blue-400 text-blue-700'
    }`;
    notification.textContent = message;
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.remove();
    }, 3000);
}
</script>
@endsection
