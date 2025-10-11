@extends('layout')

@section('title', 'Profile - ' . $user->username)

@section('content')
<div style="max-width: 600px; margin: 40px auto; background: #F5F5DC; border: 2px solid #708B75; border-radius: 12px; overflow: hidden; box-shadow: 0 8px 32px rgba(112, 139, 117, 0.2);">
    <div style="background: linear-gradient(135deg, #708B75, #5a7860); color: #F5F5DC; padding: 20px; text-align: center;">
        <h2 style="margin: 0; font-family: 'Nova Cut', serif; font-size: 24px; letter-spacing: 1px;">
            👤 User Profile
        </h2>
        <p style="margin: 8px 0 0 0; opacity: 0.9; font-size: 14px;">
            Manage your haichan identity
        </p>
    </div>
    
    <div style="padding: 30px;">
        <!-- User Info -->
        <div style="background: #FFFACD; padding: 20px; border-radius: 8px; margin-bottom: 30px; border-left: 4px solid #708B75;">
            <h3 style="margin: 0 0 15px 0; color: #3D315B; font-size: 18px;">Account Information</h3>
            
            <div style="display: grid; grid-template-columns: auto 1fr; gap: 10px 20px; align-items: center;">
                <strong>Username:</strong> <span>{{ $user->username }}</span>
                <strong>Address:</strong> <span style="font-family: monospace; font-size: 12px;">{{ substr($user->address, 0, 20) }}...</span>
                <strong>Points:</strong> <span>{{ $user->accumulated_points ?? 0 }} ⚡</span>
                <strong>Member Since:</strong> <span>{{ $user->created_at->format('M j, Y') }}</span>
            </div>
        </div>

        <!-- Avatar/Favicon Section -->
        <div style="background: #FFFACD; padding: 20px; border-radius: 8px; margin-bottom: 20px; border-left: 4px solid #708B75;">
            <h3 style="margin: 0 0 15px 0; color: #3D315B; font-size: 18px;">Chat Avatar (Favicon)</h3>
            
            <div style="display: flex; align-items: center; gap: 20px; margin-bottom: 20px;">
                <!-- Current Avatar Preview -->
                <div style="width: 64px; height: 64px; border: 2px solid #708B75; border-radius: 8px; display: flex; align-items: center; justify-content: center; background: white; overflow: hidden;">
                    @if($user->avatar_filename && Storage::disk('public')->exists('avatars/' . $user->avatar_filename))
                        <img src="{{ Storage::disk('public')->url('avatars/' . $user->avatar_filename) }}" 
                             alt="Avatar" style="width: 60px; height: 60px; object-fit: cover;">
                    @else
                        <div style="font-size: 24px;">👤</div>
                    @endif
                </div>
                
                <div>
                    <p style="margin: 0 0 8px 0; color: #3D315B; font-weight: bold;">Current Avatar</p>
                    <p style="margin: 0; color: #6B7A6B; font-size: 14px;">
                        This appears next to your messages in chat
                    </p>
                </div>
            </div>
            
            <!-- Upload Form -->
            <form id="avatar-upload-form" enctype="multipart/form-data">
                @csrf
                <div style="margin-bottom: 15px;">
                    <label for="favicon" style="display: block; margin-bottom: 8px; color: #3D315B; font-weight: 600;">
                        Upload New Avatar
                    </label>
                    <input type="file" name="favicon" id="favicon" accept="image/*" required
                           style="width: 100%; padding: 10px; border: 2px solid #708B75; border-radius: 6px; background: white; font-family: inherit;">
                    <div style="font-size: 12px; color: #6B7A6B; margin-top: 4px;">
                        PNG, JPG, GIF, WebP • Max 1MB • Will be resized to 32x32px
                    </div>
                </div>
                
                <button type="submit" id="upload-btn"
                        style="background: linear-gradient(135deg, #708B75, #5a7860); color: #F5F5DC; border: none; padding: 12px 24px; border-radius: 8px; font-size: 14px; font-weight: 600; cursor: pointer; transition: all 0.3s ease;">
                    📤 Upload Avatar
                </button>
            </form>
            
            <div id="upload-status" style="margin-top: 15px; display: none;"></div>
        </div>

        <!-- Back Navigation -->
        <div style="text-align: center;">
            <a href="{{ route('chat.index') }}" 
               style="color: #708B75; text-decoration: none; font-weight: 600; padding: 8px 16px; border: 2px solid #708B75; border-radius: 6px; transition: all 0.3s ease;">
                ← Back to Chat
            </a>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('avatar-upload-form');
    const uploadBtn = document.getElementById('upload-btn');
    const status = document.getElementById('upload-status');
    
    form.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const formData = new FormData(form);
        
        // Show loading state
        uploadBtn.textContent = '⏳ Uploading...';
        uploadBtn.disabled = true;
        status.style.display = 'block';
        status.innerHTML = '<div style="color: #ffc107;">Uploading avatar...</div>';
        
        try {
            const response = await fetch('{{ route("profile.upload-favicon") }}', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            });
            
            const result = await response.json();
            
            if (result.success) {
                status.innerHTML = '<div style="color: #28a745;">✅ ' + result.message + '</div>';
                
                // Update avatar preview after successful upload
                setTimeout(() => {
                    location.reload();
                }, 1500);
            } else {
                status.innerHTML = '<div style="color: #dc3545;">❌ ' + (result.error || 'Upload failed') + '</div>';
            }
        } catch (error) {
            status.innerHTML = '<div style="color: #dc3545;">❌ Upload failed: ' + error.message + '</div>';
        } finally {
            uploadBtn.textContent = '📤 Upload Avatar';
            uploadBtn.disabled = false;
        }
    });
});
</script>
@endsection