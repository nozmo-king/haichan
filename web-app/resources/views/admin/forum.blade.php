<!DOCTYPE html>
<html lang="en" data-theme="cyberpunk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Forum Moderation - Haichan Admin</title>
    <link rel="stylesheet" href="/css/haichan.css">
    <link rel="stylesheet" href="/css/themes.css">
    <link href="https://fonts.googleapis.com/css2?family=Nova+Cut&display=swap" rel="stylesheet">
    <style>
        .content-table {
            width: 100%;
            border-collapse: collapse;
            background: var(--content-bg);
            border-radius: 8px;
            overflow: hidden;
            border: 2px solid var(--border-color);
            margin-bottom: 20px;
        }
        .content-table th, .content-table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid var(--border-color);
            vertical-align: top;
        }
        .content-table th {
            background: var(--secondary-bg);
            color: var(--accent-color);
            font-weight: bold;
        }
        .content-preview {
            max-width: 300px;
            max-height: 60px;
            overflow: hidden;
            font-size: 12px;
            line-height: 1.4;
            color: var(--text-secondary);
        }
        .action-btn {
            padding: 6px 12px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 12px;
            text-decoration: none;
            display: inline-block;
            margin-right: 5px;
            margin-bottom: 5px;
        }
        .btn-delete { background: #F44336; color: white; }
        .btn-pin { background: #FF9800; color: white; }
        .btn-lock { background: #795548; color: white; }
        .btn-edit { background: #2196F3; color: white; }
        .btn-view { background: #4CAF50; color: white; }
        .board-tag {
            background: var(--accent-color);
            color: white;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 11px;
            font-weight: bold;
        }
        .status-badge {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 11px;
            font-weight: bold;
        }
        .status-normal { background: #4CAF50; color: white; }
        .status-pinned { background: #FF9800; color: white; }
        .status-locked { background: #795548; color: white; }
        .tab-nav {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
            background: var(--content-bg);
            padding: 15px;
            border-radius: 8px;
            border: 2px solid var(--border-color);
        }
        .tab-btn {
            padding: 10px 20px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            text-decoration: none;
            font-weight: bold;
        }
        .tab-active { background: var(--accent-color); color: white; }
        .tab-inactive { background: var(--secondary-bg); color: var(--text-secondary); }
    </style>
</head>
<body>

<div style="min-height: 100vh; background: var(--primary-bg); color: var(--text-primary); padding: 20px;">

    <!-- Header -->
    <div style="background: var(--content-bg); padding: 20px; border-radius: 12px; border: 3px solid var(--accent-color); margin-bottom: 30px; display: flex; justify-content: space-between; align-items: center;">
        <div>
            <h1 style="font-family: 'Nova Cut', serif; font-size: 32px; color: var(--accent-color); margin: 0;">
                🛡️ FORUM MODERATION
            </h1>
            <div style="color: var(--text-secondary);">
                Manage threads, posts, and content across all boards
            </div>
        </div>
        <div>
            <a href="/admin" style="background: var(--accent-color); color: white; text-decoration: none; padding: 12px 20px; border-radius: 6px;">
                ← Admin Panel
            </a>
        </div>
    </div>

    <!-- Moderation Stats -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; margin-bottom: 25px;">
        <div style="background: var(--content-bg); padding: 15px; border-radius: 8px; border: 2px solid var(--border-color); text-align: center;">
            <div style="font-size: 24px; font-weight: bold; color: var(--success-color);">{{ $stats['threads'] }}</div>
            <div style="color: var(--text-secondary); font-size: 14px;">Total Threads</div>
        </div>
        <div style="background: var(--content-bg); padding: 15px; border-radius: 8px; border: 2px solid var(--border-color); text-align: center;">
            <div style="font-size: 24px; font-weight: bold; color: var(--warning-color);">{{ $stats['posts'] }}</div>
            <div style="color: var(--text-secondary); font-size: 14px;">Total Posts</div>
        </div>
        <div style="background: var(--content-bg); padding: 15px; border-radius: 8px; border: 2px solid var(--border-color); text-align: center;">
            <div style="font-size: 24px; font-weight: bold; color: var(--accent-color);">{{ $stats['pinned'] }}</div>
            <div style="color: var(--text-secondary); font-size: 14px;">Pinned Threads</div>
        </div>
        <div style="background: var(--content-bg); padding: 15px; border-radius: 8px; border: 2px solid var(--border-color); text-align: center;">
            <div style="font-size: 24px; font-weight: bold; color: var(--highlight-color);">{{ $stats['locked'] }}</div>
            <div style="color: var(--text-secondary); font-size: 14px;">Locked Threads</div>
        </div>
    </div>

    <!-- Tab Navigation -->
    <div class="tab-nav">
        <a href="/admin/forum?tab=threads" class="tab-btn {{ request('tab', 'threads') === 'threads' ? 'tab-active' : 'tab-inactive' }}">
            📝 Recent Threads
        </a>
        <a href="/admin/forum?tab=posts" class="tab-btn {{ request('tab') === 'posts' ? 'tab-active' : 'tab-inactive' }}">
            💬 Recent Posts
        </a>
        <a href="/admin/forum?tab=reports" class="tab-btn {{ request('tab') === 'reports' ? 'tab-active' : 'tab-inactive' }}">
            🚨 Reports (0)
        </a>
    </div>

    @if(request('tab', 'threads') === 'threads')
    <!-- Recent Threads -->
    <div style="overflow-x: auto;">
        <table class="content-table">
            <thead>
                <tr>
                    <th>Thread</th>
                    <th>Board</th>
                    <th>Author</th>
                    <th>Status</th>
                    <th>Replies</th>
                    <th>Created</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($threads as $thread)
                <tr>
                    <td>
                        <div style="font-weight: bold; margin-bottom: 5px;">{{ $thread->title }}</div>
                        <div class="content-preview">{{ strip_tags($thread->content) }}</div>
                    </td>
                    <td><span class="board-tag">{{ $thread->board->code ?? 'gen' }}</span></td>
                    <td>
                        @if($thread->user_id && $thread->bitcoinUser)
                            @if($thread->bitcoinUser->admin_level >= 9)
                                <span style="color: #FF6B35;">👑</span>
                            @elseif($thread->bitcoinUser->admin_level >= 7)
                                <span style="color: #4CAF50;">🛡️</span>
                            @elseif($thread->bitcoinUser->admin_level >= 5)
                                <span style="color: #2196F3;">⚔️</span>
                            @elseif($thread->bitcoinUser->admin_level >= 1)
                                <span style="color: #FFD700;">🔱</span>
                            @endif
                            {{ $thread->bitcoinUser->username }}
                        @else
                            <span style="color: var(--text-secondary);">Anonymous</span>
                        @endif
                    </td>
                    <td>
                        @if($thread->sticky)
                            <span class="status-badge status-pinned">📌 PINNED</span>
                        @elseif($thread->locked)
                            <span class="status-badge status-locked">🔒 LOCKED</span>
                        @else
                            <span class="status-badge status-normal">✅ NORMAL</span>
                        @endif
                    </td>
                    <td style="text-align: center;">{{ $thread->reply_count ?? 0 }}</td>
                    <td style="color: var(--text-secondary); font-size: 12px;">
                        {{ $thread->created_at->format('M d, Y H:i') }}
                    </td>
                    <td>
                        <a href="/{{ $thread->board->code ?? 'gen' }}/{{ $thread->id }}" class="action-btn btn-view">View</a>
                        @if(!$thread->sticky)
                            <form method="POST" action="{{ route('admin.threads.pin', $thread->id) }}" style="display: inline;">
                                @csrf
                                <button type="submit" class="action-btn btn-pin">Pin</button>
                            </form>
                        @endif
                        @if(!$thread->locked)
                            <form method="POST" action="/admin/threads/{{ $thread->id }}/lock" style="display: inline;">
                                @csrf
                                <button type="submit" class="action-btn btn-lock">Lock</button>
                            </form>
                        @endif
                        <form method="POST" action="/admin/threads/{{ $thread->id }}/delete" style="display: inline;" onsubmit="return confirm('Delete this thread permanently?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="action-btn btn-delete">Delete</button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    @if(request('tab') === 'posts')
    <!-- Recent Posts -->
    <div style="overflow-x: auto;">
        <table class="content-table">
            <thead>
                <tr>
                    <th>Post</th>
                    <th>Thread</th>
                    <th>Board</th>
                    <th>Author</th>
                    <th>Created</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($posts as $post)
                <tr>
                    <td>
                        <div class="content-preview">{{ strip_tags($post->content) }}</div>
                    </td>
                    <td>
                        <a href="/{{ $post->thread->board->code ?? 'gen' }}/{{ $post->thread_id }}#post{{ $post->id }}"
                           style="color: var(--accent-color); text-decoration: none;">
                            {{ Str::limit($post->thread->title ?? 'Unknown Thread', 30) }}
                        </a>
                    </td>
                    <td><span class="board-tag">{{ $post->thread->board->code ?? 'gen' }}</span></td>
                    <td>
                        @if($post->user_id && $post->bitcoinUser)
                            @if($post->bitcoinUser->admin_level >= 9)
                                <span style="color: #FF6B35;">👑</span>
                            @elseif($post->bitcoinUser->admin_level >= 7)
                                <span style="color: #4CAF50;">🛡️</span>
                            @elseif($post->bitcoinUser->admin_level >= 5)
                                <span style="color: #2196F3;">⚔️</span>
                            @elseif($post->bitcoinUser->admin_level >= 1)
                                <span style="color: #FFD700;">🔱</span>
                            @endif
                            {{ $post->bitcoinUser->username }}
                        @else
                            <span style="color: var(--text-secondary);">Anonymous</span>
                        @endif
                    </td>
                    <td style="color: var(--text-secondary); font-size: 12px;">
                        {{ $post->created_at->format('M d, Y H:i') }}
                    </td>
                    <td>
                        <a href="/{{ $post->thread->board->code ?? 'gen' }}/{{ $post->thread_id }}#post{{ $post->id }}" class="action-btn btn-view">View</a>
                        <form method="POST" action="/admin/posts/{{ $post->id }}/delete" style="display: inline;" onsubmit="return confirm('Delete this post permanently?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="action-btn btn-delete">Delete</button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    @if(request('tab') === 'reports')
    <!-- Reports Section -->
    <div style="text-align: center; padding: 40px; color: var(--text-secondary);">
        <div style="font-size: 48px; margin-bottom: 10px;">🚨</div>
        <div>No reports to review</div>
        <div style="margin-top: 10px; font-size: 14px;">User reporting system coming soon</div>
    </div>
    @endif

</div>

</body>
</html>