#!/bin/bash

echo "=== Fixing Haichan Homepage ==="

cd ~/Downloads/hai3-master/web-app

# First, let's check what's in the current routes file
echo "Current routes file:"
head -10 routes/web.php

# Replace the default Laravel welcome view
echo "Creating new welcome view..."
cat > resources/views/welcome.blade.php << 'EOF'
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Haichan - A Proof-of-Work Image Board</title>
    <link rel="stylesheet" href="/css/haichan.css">
</head>
<body>
    <div class="container">
        <div class="header">
            <h1><a href="/">Haichan</a></h1>
            <p>A proof-of-work image board</p>
        </div>

        <div style="text-align: center; padding: 60px 20px;">
            <h2 style="color: #444B6E; font-size: 24pt; margin-bottom: 30px;">Welcome to Haichan</h2>
            
            <div style="background: #F5F5DC; border: 1px solid #708B75; padding: 30px; margin: 20px auto; max-width: 600px;">
                <p style="font-size: 12pt; margin-bottom: 20px; color: #444B6E;">
                    A unique image board where computational work determines thread visibility.
                </p>
                
                <div style="display: flex; justify-content: center; gap: 20px; flex-wrap: wrap; margin: 30px 0;">
                    <a href="/boards" style="background: #9AB87A; color: #444B6E; text-decoration: none; padding: 12px 24px; border-radius: 4px; font-weight: bold; font-size: 14pt;">
                        📋 Enter Boards
                    </a>
                    <a href="/mining" style="background: #708B75; color: #FFFFEE; text-decoration: none; padding: 12px 24px; border-radius: 4px; font-weight: bold; font-size: 14pt;">
                        ⛏️ Mining Dashboard
                    </a>
                </div>

                <div style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #708B75;">
                    <h3 style="color: #444B6E; margin-bottom: 15px;">Available Boards:</h3>
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 10px; text-align: left;">
                        <a href="/gen" style="color: #444B6E; text-decoration: none; padding: 8px; background: #FFFACD; border-radius: 3px;">
                            <strong>/gen/</strong> - General
                        </a>
                        <a href="/tech" style="color: #444B6E; text-decoration: none; padding: 8px; background: #FFFACD; border-radius: 3px;">
                            <strong>/tech/</strong> - Technology
                        </a>
                        <a href="/biz" style="color: #444B6E; text-decoration: none; padding: 8px; background: #FFFACD; border-radius: 3px;">
                            <strong>/biz/</strong> - Business
                        </a>
                        <a href="/film" style="color: #444B6E; text-decoration: none; padding: 8px; background: #FFFACD; border-radius: 3px;">
                            <strong>/film/</strong> - Film & TV
                        </a>
                        <a href="/x" style="color: #444B6E; text-decoration: none; padding: 8px; background: #FFFACD; border-radius: 3px;">
                            <strong>/x/</strong> - Paranormal
                        </a>
                        <a href="/lit" style="color: #444B6E; text-decoration: none; padding: 8px; background: #FFFACD; border-radius: 3px;">
                            <strong>/lit/</strong> - Literature
                        </a>
                    </div>
                </div>
            </div>

            <div style="margin-top: 40px; font-size: 10pt; color: #888;">
                <p>Threads are ranked by proof-of-work computational effort.</p>
                <p>Mine SHA256 hashes to bump threads and gain visibility.</p>
            </div>
        </div>
    </div>
</body>
</html>
EOF

# Make sure we have the correct routes file
echo "Updating routes file..."
cat > routes/web.php << 'EOF'
<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BoardController;
use App\Http\Controllers\ProofOfWorkController;

// Home page - show welcome instead of redirect
Route::get('/', function () {
    return view('welcome');
});

// Board listing
Route::get('/boards', [BoardController::class, 'index'])->name('boards.index');

// Mining dashboard
Route::get('/mining', function() {
    return view('mining.dashboard');
})->name('mining.dashboard');

// Board routes with validation
Route::group(['where' => ['board' => '^(gen|tech|biz|film|x|lit)$']], function () {
    Route::get('/{board}', [BoardController::class, 'show'])->name('boards.show');
    Route::post('/{board}', [BoardController::class, 'storeThread'])->name('threads.store');
    Route::get('/{board}/thread/{thread}', [BoardController::class, 'showThread'])
        ->where('thread', '[0-9]+')->name('threads.show');
    Route::post('/{board}/thread/{thread}', [BoardController::class, 'storePost'])
        ->where('thread', '[0-9]+')->name('posts.store');
});

// API Routes
Route::group(['prefix' => 'api'], function () {
    Route::post('/submit-proof', [ProofOfWorkController::class, 'submitProof']);
    Route::get('/mining-stats', [ProofOfWorkController::class, 'getStats']);
    Route::post('/start-mining-session', [ProofOfWorkController::class, 'startMiningSession']);
    Route::post('/end-mining-session', [ProofOfWorkController::class, 'endMiningSession']);
    Route::post('/{board}/thread/{thread}/bump', [ProofOfWorkController::class, 'bumpThread'])
        ->where(['board' => '^(gen|tech|biz|film|x|lit)$', 'thread' => '[0-9]+']);
});

Route::get('/rules', function () {
    return view('static.rules');
})->name('rules');

Route::get('/faq', function () {
    return view('static.faq');
})->name('faq');

Route::fallback(function () {
    return response()->view('errors.404', [], 404);
});
EOF

# Clear all caches to make sure routes are refreshed
echo "Clearing Laravel caches..."
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear

# Check if CSS exists, create it if not
if [ ! -f "public/css/haichan.css" ]; then
    echo "Creating CSS file..."
    mkdir -p public/css
    
    cat > public/css/haichan.css << 'EOF'
/* Haichan CSS */
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: 'Times New Roman', serif;
    background: #3D315B;
    color: #000;
    line-height: 1.4;
    font-size: 10pt;
}

.container {
    max-width: 1024px;
    margin: 0 auto;
    background: #FFFFEE;
    min-height: 100vh;
    border-left: 1px solid #444B6E;
    border-right: 1px solid #444B6E;
}

.header {
    background: #708B75;
    padding: 10px 20px;
    border-bottom: 2px solid #444B6E;
    text-align: center;
}

.header h1 {
    font-size: 24pt;
    font-weight: bold;
    color: #FFFFEE;
    text-shadow: 1px 1px 0 #444B6E;
    margin-bottom: 5px;
}

.header h1 a {
    color: #FFFFEE;
    text-decoration: none;
}

.header p {
    color: #FFFFEE;
    font-style: italic;
}

a {
    transition: all 0.2s ease;
}

a:hover {
    opacity: 0.8;
}
EOF
fi

# Test the routes
echo "Testing routes..."
php artisan route:list | grep -E "(GET|POST)" | head -10

echo ""
echo "✅ Homepage fixed!"
echo ""
echo "🌐 Now visit:"
echo "   http://localhost:8000/          - Homepage with board links"
echo "   http://localhost:8000/boards    - Board listing"
echo "   http://localhost:8000/gen       - General board"
echo ""
echo "If you're still seeing the default Laravel page:"
echo "1. Make sure you're running: php artisan serve"
echo "2. Clear your browser cache (Ctrl+F5 or Cmd+Shift+R)"
echo "3. Try visiting http://localhost:8000/boards directly"
echo ""
