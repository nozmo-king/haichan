<!DOCTYPE html>
<html lang="en" data-theme="cyberpunk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>User Management - Haichan Admin</title>
    <link rel="stylesheet" href="/css/haichan.css">
    <link rel="stylesheet" href="/css/themes.css">
    <link href="https://fonts.googleapis.com/css2?family=Nova+Cut&display=swap" rel="stylesheet">
    <style>
        .user-table {
            width: 100%;
            border-collapse: collapse;
            background: var(--content-bg);
            border-radius: 8px;
            overflow: hidden;
            border: 2px solid var(--border-color);
        }
        .user-table th, .user-table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid var(--border-color);
        }
        .user-table th {
            background: var(--secondary-bg);
            color: var(--accent-color);
            font-weight: bold;
        }
        .admin-badge {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: bold;
            display: inline-block;
        }
        .admin-9 { background: #FF6B35; color: white; }
        .admin-7 { background: #4CAF50; color: white; }
        .admin-5 { background: #2196F3; color: white; }
        .admin-1 { background: #FFD700; color: #1A1A1A; }
        .user-actions {
            display: flex;
            gap: 5px;
        }
        .action-btn {
            padding: 6px 12px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 12px;
            text-decoration: none;
            display: inline-block;
        }
        .btn-ban { background: #F44336; color: white; }
        .btn-unban { background: #4CAF50; color: white; }
        .btn-promote { background: #2196F3; color: white; }
        .btn-demote { background: #FF9800; color: white; }
    </style>
</head>
<body>

<div style="min-height: 100vh; background: var(--primary-bg); color: var(--text-primary); padding: 20px;">

    <!-- Header -->
    <div style="background: var(--content-bg); padding: 20px; border-radius: 12px; border: 3px solid var(--accent-color); margin-bottom: 30px; display: flex; justify-content: space-between; align-items: center;">
        <div>
            <h1 style="font-family: 'Nova Cut', serif; font-size: 32px; color: var(--accent-color); margin: 0;">
                👥 USER MANAGEMENT
            </h1>
            <div style="color: var(--text-secondary);">
                Total Users: {{ $users->count() }}/256 • Showing {{ $users->count() }} users
            </div>
        </div>
        <div>
            <a href="/admin" style="background: var(--accent-color); color: white; text-decoration: none; padding: 12px 20px; border-radius: 6px;">
                ← Admin Panel
            </a>
        </div>
    </div>

    <!-- User Filters -->
    <div style="background: var(--content-bg); padding: 15px; border-radius: 8px; border: 2px solid var(--border-color); margin-bottom: 20px;">
        <div style="display: flex; gap: 10px; flex-wrap: wrap;">
            <a href="/admin/users" class="action-btn btn-promote">All Users</a>
            <a href="/admin/users?filter=admins" class="action-btn" style="background: #FFD700; color: #1A1A1A;">Admins Only</a>
            <a href="/admin/users?filter=banned" class="action-btn btn-ban">Banned Users</a>
            <a href="/admin/users?filter=recent" class="action-btn" style="background: #9C27B0; color: white;">Recent (7 days)</a>
        </div>
    </div>

    <!-- Users Table -->
    <div style="overflow-x: auto;">
        <table class="user-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Username</th>
                    <th>Bitcoin Address</th>
                    <th>Admin Level</th>
                    <th>Status</th>
                    <th>Registered</th>
                    <th>Last Login</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($users as $user)
                <tr>
                    <td><strong>#{{ $user->id }}</strong></td>
                    <td>{{ $user->username }}</td>
                    <td style="font-family: 'Courier New', monospace; font-size: 12px;">
                        {{ substr($user->address, 0, 8) }}...{{ substr($user->address, -8) }}
                    </td>
                    <td>
                        @if($user->admin_level >= 9)
                            <span class="admin-badge admin-9">👑 SUPER ADMIN ({{ $user->admin_level }})</span>
                        @elseif($user->admin_level >= 7)
                            <span class="admin-badge admin-7">🛡️ SUPER MOD ({{ $user->admin_level }})</span>
                        @elseif($user->admin_level >= 5)
                            <span class="admin-badge admin-5">⚔️ MODERATOR ({{ $user->admin_level }})</span>
                        @elseif($user->admin_level >= 1)
                            <span class="admin-badge admin-1">🔱 ADMIN ({{ $user->admin_level }})</span>
                        @else
                            <span style="color: var(--text-secondary);">User (0)</span>
                        @endif
                    </td>
                    <td>
                        @if($user->is_banned)
                            <span style="color: #F44336; font-weight: bold;">🚫 BANNED</span>
                            @if($user->banned_until)
                                <br><small style="color: var(--text-secondary);">Until: {{ $user->banned_until->format('M d, Y') }}</small>
                            @endif
                        @else
                            <span style="color: #4CAF50;">✅ Active</span>
                        @endif
                    </td>
                    <td style="color: var(--text-secondary); font-size: 12px;">
                        {{ $user->created_at->format('M d, Y') }}
                        <br><small>{{ $user->created_at->diffForHumans() }}</small>
                    </td>
                    <td style="color: var(--text-secondary); font-size: 12px;">
                        @if($user->last_login)
                            {{ $user->last_login->format('M d, Y') }}
                            <br><small>{{ $user->last_login->diffForHumans() }}</small>
                        @else
                            Never
                        @endif
                    </td>
                    <td>
                        <div class="user-actions">
                            @if($user->is_banned)
                                <form method="POST" action="/admin/users/{{ $user->id }}/unban" style="display: inline;">
                                    @csrf
                                    <button type="submit" class="action-btn btn-unban">Unban</button>
                                </form>
                            @else
                                <a href="/admin/users/{{ $user->id }}/ban" class="action-btn btn-ban">Ban</a>
                            @endif

                            @if($user->admin_level < 5)
                                <form method="POST" action="/admin/users/{{ $user->id }}/promote" style="display: inline;">
                                    @csrf
                                    <button type="submit" class="action-btn btn-promote">Promote</button>
                                </form>
                            @endif

                            @if($user->admin_level > 0)
                                <form method="POST" action="/admin/users/{{ $user->id }}/demote" style="display: inline;">
                                    @csrf
                                    <button type="submit" class="action-btn btn-demote">Demote</button>
                                </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    @if($users->isEmpty())
    <div style="text-align: center; padding: 40px; color: var(--text-secondary);">
        <div style="font-size: 48px; margin-bottom: 10px;">👥</div>
        <div>No users found matching the current filter</div>
    </div>
    @endif

</div>

</body>
</html>