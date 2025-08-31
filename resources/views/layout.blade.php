<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Anonymous Forum')</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body {
            font-family: 'Courier New', monospace;
            background-color: #f0e0d6;
            margin: 0;
            padding: 20px;
            color: #000;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            background-color: #fff;
            border: 1px solid #ccc;
            padding: 30px;
            min-height: 100vh;
        }
        
        .header {
            text-align: center;
            margin-bottom: 40px;
            border-bottom: 2px solid #ccc;
            padding-bottom: 30px;
        }
        
        .board-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
            gap: 20px;
            margin: 30px 0;
        }
        
        .board-item {
            border: 1px solid #ccc;
            padding: 15px;
            background-color: #f9f9f9;
        }
        
        .board-code {
            font-size: 24px;
            font-weight: bold;
            color: #789922;
            margin-bottom: 10px;
        }
        
        .board-name {
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        
        .board-desc {
            font-size: 14px;
            color: #666;
            margin-bottom: 10px;
        }
        
        .board-stats {
            font-size: 12px;
            color: #888;
        }
        
        .thread-list {
            border-collapse: collapse;
            width: 100%;
            margin-top: 20px;
        }
        
        .thread-list th,
        .thread-list td {
            border: 1px solid #ccc;
            padding: 8px;
            text-align: left;
        }
        
        .thread-list th {
            background-color: #f0f0f0;
            font-weight: bold;
        }
        
        .thread-title {
            font-weight: bold;
        }
        
        .thread-meta {
            font-size: 12px;
            color: #666;
        }
        
        .post {
            border: 1px solid #ccc;
            margin: 10px 0;
            padding: 15px;
            background-color: #f9f9f9;
        }
        
        .post-header {
            font-size: 12px;
            color: #117743;
            font-weight: bold;
            margin-bottom: 10px;
        }
        
        .post-content {
            line-height: 1.4;
        }
        
        a {
            color: #34345c;
            text-decoration: underline;
        }
        
        a:hover {
            color: #dd0000;
        }
        
        .breadcrumb {
            margin-bottom: 20px;
            font-size: 14px;
        }
        
        .breadcrumb a {
            color: #34345c;
        }
        
        .thread-preview {
            padding: 15px;
            border-bottom: 1px solid #ddd;
        }
        
        .thread-header {
            margin-bottom: 10px;
        }
        
        .post-preview {
            margin-bottom: 15px;
            font-style: italic;
            color: #333;
        }
        
        .replies-preview {
            margin-left: 20px;
        }
        
        .reply-preview {
            margin: 8px 0;
            font-size: 14px;
            color: #555;
        }
        
        .reply-author {
            font-weight: bold;
            color: #117743;
        }
        
        .nested-reply {
            margin-left: 25px;
            margin-top: 5px;
            font-size: 13px;
            color: #666;
        }
        
        .more-replies, .more-posts {
            margin-top: 10px;
            font-size: 12px;
            color: #888;
        }
        
        .more-posts a {
            color: #789922;
            font-weight: bold;
        }
        
        .reply-form {
            margin: 20px 0;
            padding: 15px;
            background-color: #f5f5f5;
            border: 1px solid #ccc;
        }
        
        .reply-form input, .reply-form textarea {
            width: 100%;
            padding: 8px;
            margin: 5px 0;
            border: 1px solid #ccc;
            font-family: 'Courier New', monospace;
        }
        
        .reply-form button {
            padding: 8px 15px;
            background-color: #789922;
            color: white;
            border: none;
            cursor: pointer;
        }
        
        .reply-form button:hover {
            background-color: #5a7019;
        }
        
        .nested-post {
            margin-left: 30px;
            border-left: 3px solid #ccc;
            padding-left: 15px;
        }
        
        .post-image, .thread-image {
            margin: 10px 0;
        }
        
        .post-image img, .thread-image img {
            border: 1px solid #ccc;
            transition: all 0.3s ease;
        }
        
        .post-image img:hover, .thread-image img:hover {
            border-color: #789922;
        }
        
        .form-group input[type="file"] {
            background-color: #f9f9f9;
            border: 1px solid #ccc;
            font-family: 'Courier New', monospace;
            font-size: 12px;
        }
        
        .thread-preview-image, .post-preview-image, .reply-preview-image {
            display: inline-block;
            flex-shrink: 0;
        }
        
        .thread-preview-image img, .post-preview-image img, .reply-preview-image img {
            border-radius: 3px;
            transition: all 0.3s ease;
            cursor: pointer;
            object-fit: cover;
        }
        
        .thread-preview-image img:hover, .post-preview-image img:hover, .reply-preview-image img:hover {
            border-color: #789922;
            transform: scale(1.15);
            box-shadow: 0 2px 8px rgba(0,0,0,0.2);
        }
        
        .thread-preview-image img {
            width: 180px;
            height: 180px;
        }
        
        .post-preview-image img {
            width: 120px;
            height: 120px;
        }
        
        .reply-preview-image img {
            width: 80px;
            height: 80px;
        }
        
        .reply-preview {
            margin: 20px 0;
            font-size: 15px;
            color: #555;
            line-height: 1.6;
            padding: 15px 0;
            border-bottom: 1px solid #ddd;
            min-height: 60px;
        }
        
        .nested-reply {
            margin-left: 35px;
            margin-top: 12px;
            font-size: 14px;
            color: #666;
            line-height: 1.5;
            padding: 10px 0;
            border-left: 3px solid #e8e8e8;
            padding-left: 15px;
            background-color: #fefefe;
        }
        
        .deeply-nested-reply {
            margin-left: 40px;
            margin-top: 10px;
            font-size: 13px;
            color: #777;
            line-height: 1.4;
            padding: 8px 0;
            border-left: 3px solid #f0f0f0;
            padding-left: 12px;
            background-color: #fdfdfd;
            min-height: 30px;
        }
        
        .deeply-nested-reply img {
            width: 60px;
            height: 60px;
        }
        
        .thread-preview {
            padding: 25px;
            border-bottom: 2px solid #ddd;
            min-height: 200px;
            margin-bottom: 20px;
        }
        
        .post-preview {
            margin-bottom: 25px;
            font-style: italic;
            color: #333;
            line-height: 1.7;
            padding: 20px;
            background-color: #fafafa;
            border-left: 4px solid #789922;
            font-size: 15px;
        }
        
        .replies-preview {
            margin-left: 30px;
            margin-top: 25px;
            padding: 20px;
            background-color: #f8f8f8;
            border-radius: 5px;
            border: 1px solid #e8e8e8;
        }
        
        .more-posts a {
            color: #789922;
            font-weight: bold;
            text-decoration: none;
            padding: 5px 10px;
            background-color: #f0f0f0;
            border-radius: 3px;
            display: inline-block;
            margin-top: 10px;
        }
        
        .more-posts a:hover {
            background-color: #789922;
            color: white;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1><a href="{{ route('forum.index') }}" style="text-decoration: none; color: inherit;">Anonymous Forum</a></h1>
            
            @auth
                <!-- Navigation Menu -->
                <div style="margin: 15px 0; text-align: center;">
                    <a href="{{ route('forum.index') }}" style="margin: 0 10px; padding: 5px 10px; background-color: #f0f0f0; text-decoration: none; border-radius: 3px;">Forum</a>
                    <a href="{{ route('subscription.dashboard') }}" style="margin: 0 10px; padding: 5px 10px; background-color: #f0f0f0; text-decoration: none; border-radius: 3px;">My Subscription</a>
                    <a href="{{ route('friend-codes.index') }}" style="margin: 0 10px; padding: 5px 10px; background-color: #f0f0f0; text-decoration: none; border-radius: 3px;">Friend Codes</a>
                    <a href="{{ route('subscription.plans') }}" style="margin: 0 10px; padding: 5px 10px; background-color: #f0f0f0; text-decoration: none; border-radius: 3px;">Plans</a>
                </div>
                
                <!-- User Info and Subscription Status -->
                <div style="text-align: right; margin-top: 10px; font-size: 12px;">
                    <div style="margin-bottom: 5px;">
                        Logged in as: <strong>{{ substr(auth()->user()->allowedPublicKey->public_key, 0, 12) }}...</strong>
                        @if(auth()->user()->hasActiveSubscription())
                            <span style="color: #28a745; margin-left: 10px;">✓ Active Subscription</span>
                        @else
                            <span style="color: #dc3545; margin-left: 10px;">⚠ No Active Subscription</span>
                        @endif
                    </div>
                    <form method="POST" action="{{ route('auth.logout') }}" style="display: inline;">
                        @csrf
                        <button type="submit" style="background: none; border: none; color: #dd0000; text-decoration: underline; cursor: pointer; font-size: 12px;">[Logout]</button>
                    </form>
                </div>
            @else
                <!-- Guest Navigation -->
                <div style="margin: 15px 0; text-align: center;">
                    <a href="{{ route('auth.login') }}" style="margin: 0 10px; padding: 5px 10px; background-color: #789922; color: white; text-decoration: none; border-radius: 3px;">Login</a>
                    <a href="{{ route('auth.register.form') }}" style="margin: 0 10px; padding: 5px 10px; background-color: #34345c; color: white; text-decoration: none; border-radius: 3px;">Register</a>
                    <a href="{{ route('subscription.plans') }}" style="margin: 0 10px; padding: 5px 10px; background-color: #f0f0f0; text-decoration: none; border-radius: 3px;">View Plans</a>
                </div>
                <div style="text-align: center; font-size: 12px; color: #666; margin-top: 10px;">
                    Need an account? You'll need a friend code to register.
                </div>
            @endauth
        </div>
        
        @yield('content')
    </div>
</body>
</html>