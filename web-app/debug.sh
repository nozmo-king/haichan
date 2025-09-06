#!/bin/bash

echo "=== Debugging and Fixing Routes ==="

cd ~/Downloads/hai3-master/web-app

# First, let's see what routes are actually registered
echo "Current registered routes:"
php artisan route:list

echo ""
echo "Checking if boards exist in database..."
php artisan tinker --execute="
try {
    echo 'Total boards: ' . App\Models\Board::count() . PHP_EOL;
    \$boards = App\Models\Board::all();
    foreach(\$boards as \$board) {
        echo 'Board: ' . \$board->name . ' (ID: ' . \$board->id . ')' . PHP_EOL;
    }
} catch (Exception \$e) {
    echo 'Error: ' . \$e->getMessage() . PHP_EOL;
}
"

echo ""
echo "Creating a completely fresh routes file..."

# Create a super simple routes file first to test
cat > routes/web.php << 'EOF'
<?php

use Illuminate\Support\Facades\Route;

// Simple test routes first
Route::get('/', function () {
    return view('welcome');
});

Route::get('/test', function () {
    return response()->json([
        'status' => 'Routes working!',
        'boards_count' => \App\Models\Board::count(),
        'boards' => \App\Models\Board::pluck('name')
    ]);
});

Route::get('/boards', function () {
    try {
        $boards = \App\Models\Board::where('active', true)->get();
        return view('boards.index', compact('boards'));
    } catch (Exception $e) {
        return response()->json(['error' => $e->getMessage()]);
    }
});

// Individual board routes (simplified)
Route::get('/gen', function () {
    try {
        $board = \App\Models\Board::where('name', 'gen')->firstOrFail();
        $threads = $board->threads()->orderBy('created_at', 'desc')->paginate(20);
        return view('boards.show', compact('board', 'threads'));
    } catch (Exception $e) {
        return response()->json(['error' => $e->getMessage()]);
    }
});

Route::get('/tech', function () {
    try {
        $board = \App\Models\Board::where('name', 'tech')->firstOrFail();
        $threads = $board->threads()->orderBy('created_at', 'desc')->paginate(20);
        return view('boards.show', compact('board', 'threads'));
    } catch (Exception $e) {
        return response()->json(['error' => $e->getMessage()]);
    }
});

Route::get('/biz', function () {
    try {
        $board = \App\Models\Board::where('name', 'biz')->firstOrFail();
        $threads = $board->threads()->orderBy('created_at', 'desc')->paginate(20);
        return view('boards.show', compact('board', 'threads'));
    } catch (Exception $e) {
        return response()->json(['error' => $e->getMessage()]);
    }
});

Route::get('/film', function () {
    try {
        $board = \App\Models\Board::where('name', 'film')->firstOrFail();
        $threads = $board->threads()->orderBy('created_at', 'desc')->paginate(20);
        return view('boards.show', compact('board', 'threads'));
    } catch (Exception $e) {
        return response()->json(['error' => $e->getMessage()]);
    }
});

Route::get('/x', function () {
    try {
        $board = \App\Models\Board::where('name', 'x')->firstOrFail();
        $threads = $board->threads()->orderBy('created_at', 'desc')->paginate(20);
        return view('boards.show', compact('board', 'threads'));
    } catch (Exception $e) {
        return response()->json(['error' => $e->getMessage()]);
    }
});

Route::get('/lit', function () {
    try {
        $board = \App\Models\Board::where('name', 'lit')->firstOrFail();
        $threads = $board->threads()->orderBy('created_at', 'desc')->paginate(20);
        return view('boards.show', compact('board', 'threads'));
    } catch (Exception $e) {
        return response()->json(['error' => $e->getMessage()]);
    }
});

Route::get('/mining', function() {
    return view('mining.dashboard');
});

// Fallback
Route::fallback(function () {
    return response()->view('errors.404', [], 404);
});
EOF

echo "✓ Simple routes created"

# Clear all caches aggressively
echo "Clearing all caches..."
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear

# Check if the Board model exists and works
echo ""
echo "Testing Board model..."
php artisan tinker --execute="
try {
    \$board = new App\Models\Board();
    echo 'Board model loaded successfully' . PHP_EOL;
    
    \$boards = App\Models\Board::all();
    echo 'Found ' . \$boards->count() . ' boards' . PHP_EOL;
    
    foreach(\$boards as \$board) {
        echo '- ' . \$board->name . ': ' . \$board->description . PHP_EOL;
    }
} catch (Exception \$e) {
    echo 'Board model error: ' . \$e->getMessage() . PHP_EOL;
}
"

# Check if views exist
echo ""
echo "Checking if views exist..."
if [ -f "resources/views/welcome.blade.php" ]; then
    echo "✓ welcome.blade.php exists"
else
    echo "✗ welcome.blade.php missing - creating..."
    cat > resources/views/welcome.blade.php << 'EOF'
<!DOCTYPE html>
<html>
<head>
    <title>Haichan</title>
    <link rel="stylesheet" href="/css/haichan.css">
</head>
<body>
    <div class="container">
        <div class="header">
            <h1><a href="/">Haichan</a></h1>
            <p>A proof-of-work image board</p>
        </div>
        <div style="text-align: center; padding: 50px;">
            <h2>Welcome to Haichan</h2>
            <p><a href="/boards">View Boards</a> | <a href="/mining">Mining Dashboard</a></p>
            <hr>
            <p>Direct board links:</p>
            <p>
                <a href="/gen">/gen/</a> | 
                <a href="/tech">/tech/</a> | 
                <a href="/biz">/biz/</a> | 
                <a href="/film">/film/</a> | 
                <a href="/x">/x/</a> | 
                <a href="/lit">/lit/</a>
            </p>
        </div>
    </div>
</body>
</html>
EOF
fi

if [ -f "resources/views/boards/index.blade.php" ]; then
    echo "✓ boards/index.blade.php exists"
else
    echo "✗ boards/index.blade.php missing - creating..."
    mkdir -p resources/views/boards
    cat > resources/views/boards/index.blade.php << 'EOF'
<!DOCTYPE html>
<html>
<head>
    <title>Boards - Haichan</title>
    <link rel="stylesheet" href="/css/haichan.css">
</head>
<body>
    <div class="container">
        <div class="header">
            <h1><a href="/">Haichan</a></h1>
            <p>A proof-of-work image board</p>
        </div>
        <div style="padding: 20px;">
            <h2>Boards</h2>
            @if($boards->count() > 0)
                @foreach($boards as $board)
                <div style="margin: 20px 0; padding: 15px; border: 1px solid #ccc;">
                    <h3><a href="/{{ $board->name }}">{{ $board->title }}</a></h3>
                    <p>{{ $board->description }}</p>
                </div>
                @endforeach
            @else
                <p>No boards found.</p>
            @endif
        </div>
    </div>
</body>
</html>
EOF
fi

if [ -f "resources/views/boards/show.blade.php" ]; then
    echo "✓ boards/show.blade.php exists"
else
    echo "✗ boards/show.blade.php missing - creating..."
    cat > resources/views/boards/show.blade.php << 'EOF'
<!DOCTYPE html>
<html>
<head>
    <title>{{ $board->title ?? $board->name }} - Haichan</title>
    <link rel="stylesheet" href="/css/haichan.css">
</head>
<body>
    <div class="container">
        <div class="header">
            <h1><a href="/">Haichan</a></h1>
            <nav>
                <a href="/boards">Boards</a> | <a href="/mining">Mining</a>
            </nav>
        </div>
        <div style="padding: 20px;">
            <h2>{{ $board->title ?? $board->name }}</h2>
            <p>{{ $board->description }}</p>
            
            <h3>Threads</h3>
            @if($threads->count() > 0)
                @foreach($threads as $thread)
                <div style="margin: 15px 0; padding: 10px; border: 1px solid #ccc;">
                    <strong>{{ $thread->subject ?: 'No Subject' }}</strong><br>
                    <small>{{ $thread->created_at->format('Y-m-d H:i') }} | Replies: {{ $thread->reply_count ?? 0 }}</small><br>
                    {{ Str::limit($thread->content, 200) }}
                </div>
                @endforeach
            @else
                <p>No threads yet. Be the first to post!</p>
            @endif
        </div>
    </div>
</body>
</html>
EOF
fi

# Check for CSS
if [ ! -f "public/css/haichan.css" ]; then
    echo "✗ CSS missing - creating basic CSS..."
    mkdir -p public/css
    cat > public/css/haichan.css << 'EOF'
body {
    font-family: 'Times New Roman', serif;
    background: #3D315B;
    color: #000;
    margin: 0;
    padding: 0;
}

.container {
    max-width: 1024px;
    margin: 0 auto;
    background: #FFFFEE;
    min-height: 100vh;
}

.header {
    background: #708B75;
    padding: 15px;
    text-align: center;
    color: #FFFFEE;
}

.header h1 a {
    color: #FFFFEE;
    text-decoration: none;
}

a {
    color: #444B6E;
    text-decoration: none;
}

a:hover {
    text-decoration: underline;
}
EOF
fi

echo ""
echo "Testing routes after fixes..."
php artisan route:list | head -10

echo ""
echo "✅ Debug and Fix Complete!"
echo ""
echo "🧪 TEST THESE URLs:"
echo "   http://localhost:8000/test        - Route test (should show JSON)"
echo "   http://localhost:8000/            - Homepage"  
echo "   http://localhost:8000/boards      - Board listing"
echo "   http://localhost:8000/gen         - General board"
echo "   http://localhost:8000/mining      - Mining dashboard"
echo ""
echo "If you still get 404 errors:"
echo "1. Make sure Laravel server is running: php artisan serve"
echo "2. Check the URL in your browser matches exactly"
echo "3. Try the /test route first to see if basic routing works"
echo ""
echo "If /test works but /boards doesn't, there's a model/database issue."
echo "If /test doesn't work, there's a routing/Laravel issue."
echo ""
