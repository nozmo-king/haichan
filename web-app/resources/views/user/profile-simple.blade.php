@extends('layout')

@section('title', $user->username . ' - User Profile')

@section('content')
<div style="margin: 20px auto; max-width: 800px;">
    <div style="background: #F5F5DC; border: 2px solid #708B75; border-radius: 8px; padding: 30px;">
        
        <!-- Profile Header -->
        <div style="text-align: center; margin-bottom: 30px;">
            @if($user->avatar_path)
                <img src="{{ Storage::url($user->avatar_path) }}" alt="Avatar" 
                     style="width: 100px; height: 100px; border-radius: 50%; border: 3px solid #708B75; margin-bottom: 15px;">
            @else
                <div style="width: 100px; height: 100px; border-radius: 50%; background: #708B75; display: inline-flex; align-items: center; justify-content: center; font-size: 36px; color: #F5F5DC; margin-bottom: 15px;">
                    {{ strtoupper(substr($user->username, 0, 1)) }}
                </div>
            @endif
            
            <h2 style="margin: 0; color: #3D315B;">{{ $user->username }}</h2>
            <p style="color: #6B7A6B; font-size: 12px; margin-top: 5px;">
                User #{{ $user->id }}/256 · Joined {{ $user->created_at->diffForHumans() }}
            </p>
        </div>
        
        <!-- User Stats -->
        <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 15px; margin-bottom: 30px;">
            <div style="text-align: center; padding: 15px; background: #FFFACD; border: 1px solid #9AB87A; border-radius: 4px;">
                <div style="font-size: 24px; font-weight: bold; color: #708B75;">{{ $stats['threads'] ?? 0 }}</div>
                <div style="font-size: 11px; color: #6B7A6B; text-transform: uppercase;">Threads</div>
            </div>
            <div style="text-align: center; padding: 15px; background: #FFFACD; border: 1px solid #9AB87A; border-radius: 4px;">
                <div style="font-size: 24px; font-weight: bold; color: #9AB87A;">{{ $stats['posts'] ?? 0 }}</div>
                <div style="font-size: 11px; color: #6B7A6B; text-transform: uppercase;">Posts</div>
            </div>
            <div style="text-align: center; padding: 15px; background: #FFFACD; border: 1px solid #9AB87A; border-radius: 4px;">
                <div style="font-size: 24px; font-weight: bold; color: #CD5C5C;">{{ $user->total_pow_points ?? 0 }}</div>
                <div style="font-size: 11px; color: #6B7A6B; text-transform: uppercase;">PoW Points</div>
            </div>
        </div>
        
        <!-- User Info -->
        <div style="background: #FFFACD; border: 1px solid #708B75; border-radius: 4px; padding: 20px;">
            <h3 style="margin: 0 0 15px 0; color: #3D315B; font-size: 16px;">User Information</h3>
            
            <div style="margin-bottom: 10px;">
                <span style="color: #6B7A6B; font-size: 12px;">Bitcoin Address:</span><br>
                <span style="font-family: monospace; font-size: 11px; color: #3D315B;">{{ $user->address }}</span>
            </div>
            
            <div style="margin-bottom: 10px;">
                <span style="color: #6B7A6B; font-size: 12px;">Invite Code:</span><br>
                <span style="font-family: monospace; font-size: 14px; color: #708B75; font-weight: bold;">{{ $user->invite_code }}</span>
            </div>
            
            @if($user->ssh_key)
            <div style="margin-bottom: 10px;">
                <span style="color: #6B7A6B; font-size: 12px;">SSH Key:</span><br>
                <div style="background: #F5F5DC; padding: 10px; border: 1px solid #708B75; border-radius: 4px; font-family: monospace; font-size: 10px; word-break: break-all; margin-top: 5px;">
                    {{ substr($user->ssh_key, 0, 50) }}...
                </div>
            </div>
            @endif
            
            @if($user->bio)
            <div style="margin-top: 20px; padding-top: 20px; border-top: 1px solid #708B75;">
                <span style="color: #6B7A6B; font-size: 12px;">Bio:</span><br>
                <p style="margin: 5px 0 0 0; color: #3D315B; font-size: 14px;">{{ $user->bio }}</p>
            </div>
            @endif
        </div>
        
        @if(session('bitcoin_auth_id') == $user->id)
        <div style="text-align: center; margin-top: 20px;">
            <a href="/user/profile/edit" style="display: inline-block; padding: 10px 20px; background: #708B75; color: #F5F5DC; text-decoration: none; border-radius: 4px; font-size: 14px;">
                Edit Profile
            </a>
        </div>
        @endif
        
    </div>
</div>
@endsection