#!/bin/bash

# =================================================================
# HAICHAN LOCAL DEVELOPMENT SETUP
# =================================================================
# 
# This script sets up Haichan for local development on your machine.
# Run this in your existing web-app directory.
#
# Usage: cd /root/haichan/web-app && ./local_setup.sh
# =================================================================

echo "=== Haichan Local Development Setup ==="

# Check if we're in the right directory
if [ ! -f "artisan" ]; then
    echo "Error: Please run this script from your Laravel project root directory"
    echo "Expected: /root/haichan/web-app/"
    exit 1
fi

echo "✓ Found Laravel project"

# =================================================================
# 1. CLEAN UP EXISTING ISSUES
# =================================================================

echo "Cleaning up existing files..."

# Remove corrupted routes if they exist
if grep -q "echo" routes/web.php; then
    echo "Fixing corrupted routes file..."
    cat > routes/web.php << 'EOF'
<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BoardController;
use App\Http\Controllers\ProofOfWorkController;

// Home page - redirects to boards
Route::get('/', function () {
    return redirect('/boards');
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
    echo "✓ Routes file fixed"
fi

# Clear all caches
php artisan config:clear
php artisan route:clear
php artisan view:clear

echo "✓ Cleared Laravel caches"

# =================================================================
# 2. CREATE DIRECTORY STRUCTURE
# =================================================================

echo "Setting up directory structure..."

# Create necessary directories
mkdir -p app/Models
mkdir -p app/Http/Controllers
mkdir -p database/migrations
mkdir -p resources/views/boards
mkdir -p resources/views/static
mkdir -p resources/views/errors
mkdir -p resources/views/mining
mkdir -p public/css
mkdir -p storage/app/public/images
mkdir -p storage/app/public/thumbs

echo "✓ Directories created"

# =================================================================
# 3. CREATE DATABASE MIGRATIONS
# =================================================================

echo "Creating database migrations..."

# Create boards migration
cat > database/migrations/2024_01_01_000001_create_boards_table.php << 'EOF'
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('boards', function (Blueprint $table) {
            $table->id();
            $table->string('name', 10)->unique();
            $table->string('title', 100);
            $table->text('description')->nullable();
            $table->boolean('active')->default(true);
            $table->integer('post_count')->default(0);
            $table->timestamp('last_post_at')->nullable();
            $table->timestamps();
            
            $table->index(['active', 'name']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('boards');
    }
};
EOF

# Create threads migration
cat > database/migrations/2024_01_01_000002_create_threads_table.php << 'EOF'
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('threads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('board_id')->constrained()->onDelete('cascade');
            $table->string('subject', 200)->nullable();
            $table->text('content');
            $table->string('image_filename')->nullable();
            $table->string('image_original_name')->nullable();
            $table->integer('image_size')->nullable();
            $table->boolean('sticky')->default(false);
            $table->boolean('locked')->default(false);
            $table->integer('reply_count')->default(0);
            $table->integer('image_count')->default(0);
            $table->bigInteger('bump_score')->default(0);
            $table->timestamp('bumped_at')->nullable();
            $table->string('ip_address');
            $table->string('poster_hash', 8);
            $table->timestamps();

            $table->index(['board_id', 'bumped_at']);
            $table->index(['board_id', 'sticky', 'bumped_at']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('threads');
    }
};
EOF

# Create posts migration
cat > database/migrations/2024_01_01_000003_create_posts_table.php << 'EOF'
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('posts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('thread_id')->constrained()->onDelete('cascade');
            $table->text('content');
            $table->string('image_filename')->nullable();
            $table->string('image_original_name')->nullable();
            $table->integer('image_size')->nullable();
            $table->string('ip_address');
            $table->string('poster_hash', 8);
            $table->timestamps();

            $table->index(['thread_id', 'created_at']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('posts');
    }
};
EOF

# Create proof_of_works migration
cat > database/migrations/2024_01_01_000004_create_proof_of_works_table.php << 'EOF'
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('proof_of_works', function (Blueprint $table) {
            $table->id();
            $table->foreignId('thread_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('hash', 64)->unique();
            $table->bigInteger('nonce');
            $table->text('data');
            $table->string('pattern', 20);
            $table->integer('points')->default(0);
            $table->timestamp('verified_at')->nullable();
            $table->string('ip_address');
            $table->timestamps();

            $table->index(['pattern', 'created_at']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('proof_of_works');
    }
};
EOF

echo "✓ Database migrations created"

# =================================================================
# 4. CREATE MODELS
# =================================================================

echo "Creating Eloquent models..."

# Board model
cat > app/Models/Board.php << 'EOF'
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Board extends Model
{
    protected $fillable = [
        'name', 'title', 'description', 'active', 
        'post_count', 'last_post_at'
    ];

    protected $casts = [
        'active' => 'boolean',
        'post_count' => 'integer',
        'last_post_at' => 'datetime'
    ];

    public function threads()
    {
        return $this->hasMany(Thread::class);
    }

    public function getUrlAttribute()
    {
        return "/{$this->name}";
    }

    public function incrementPostCount()
    {
        $this->increment('post_count');
        $this->update(['last_post_at' => now()]);
    }
}
EOF

# Thread model
cat > app/Models/Thread.php << 'EOF'
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Thread extends Model
{
    protected $fillable = [
        'board_id', 'subject', 'content', 
        'image_filename', 'image_original_name', 'image_size',
        'sticky', 'locked', 'reply_count', 'image_count',
        'bump_score', 'bumped_at', 'ip_address', 'poster_hash'
    ];

    protected $casts = [
        'sticky' => 'boolean',
        'locked' => 'boolean',
        'reply_count' => 'integer',
        'image_count' => 'integer',
        'bump_score' => 'integer',
        'bumped_at' => 'datetime'
    ];

    public function board()
    {
        return $this->belongsTo(Board::class);
    }

    public function posts()
    {
        return $this->hasMany(Post::class)->orderBy('created_at', 'asc');
    }

    public function getUrlAttribute()
    {
        return "/{$this->board->name}/thread/{$this->id}";
    }

    public function getImageUrlAttribute()
    {
        return $this->image_filename ? "/storage/images/{$this->image_filename}" : null;
    }

    public function getThumbnailUrlAttribute()
    {
        return $this->image_filename ? "/storage/thumbs/{$this->image_filename}" : null;
    }

    public function addReply(Post $post)
    {
        $this->increment('reply_count');
        
        if ($post->image_filename) {
            $this->increment('image_count');
        }
        
        if ($this->reply_count < 500) {
            $this->update(['bumped_at' => now()]);
        }

        $this->board->incrementPostCount();
    }

    public static function generatePosterHash($ip, $threadId)
    {
        return substr(hash('sha256', $ip . $threadId . config('app.key', 'haichan')), 0, 8);
    }
}
EOF

# Post model
cat > app/Models/Post.php << 'EOF'
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    protected $fillable = [
        'thread_id', 'content', 
        'image_filename', 'image_original_name', 'image_size',
        'ip_address', 'poster_hash'
    ];

    protected $casts = [
        'image_size' => 'integer'
    ];

    public function thread()
    {
        return $this->belongsTo(Thread::class);
    }

    public function getImageUrlAttribute()
    {
        return $this->image_filename ? "/storage/images/{$this->image_filename}" : null;
    }

    public function getThumbnailUrlAttribute()
    {
        return $this->image_filename ? "/storage/thumbs/{$this->image_filename}" : null;
    }

    public function getFormattedContentAttribute()
    {
        $content = htmlspecialchars($this->content);
        
        // Convert >>123456 to links
        $content = preg_replace('/&gt;&gt;(\d+)/', '<a href="#post$1" class="quote-link">&gt;&gt;$1</a>', $content);
        
        // Convert >greentext
        $content = preg_replace('/^&gt;(.+)/m', '<span class="greentext">&gt;$1</span>', $content);
        
        // Convert line breaks
        $content = nl2br($content);
        
        return $content;
    }
}
EOF

# ProofOfWork model
cat > app/Models/ProofOfWork.php << 'EOF'
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProofOfWork extends Model
{
    protected $fillable = [
        'thread_id', 'hash', 'nonce', 'data', 
        'pattern', 'points', 'verified_at', 'ip_address'
    ];

    protected $casts = [
        'nonce' => 'integer',
        'points' => 'integer',
        'verified_at' => 'datetime'
    ];

    public function thread()
    {
        return $this->belongsTo(Thread::class);
    }
}
EOF

echo "✓ Models created"

# =================================================================
# 5. CREATE CONTROLLERS
# =================================================================

echo "Creating controllers..."

# BoardController
cat > app/Http/Controllers/BoardController.php << 'EOF'
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Models\Board;
use App\Models\Thread;
use App\Models\Post;

class BoardController extends Controller
{
    public function index()
    {
        $boards = Board::where('active', true)
            ->orderBy('name')
            ->withCount('threads')
            ->get();

        return view('boards.index', compact('boards'));
    }

    public function show(Request $request, $boardName)
    {
        $board = Board::where('name', $boardName)->where('active', true)->firstOrFail();
        
        $threads = $board->threads()
            ->with(['posts' => function($query) {
                $query->latest()->limit(3);
            }])
            ->orderBy('sticky', 'desc')
            ->orderBy('bumped_at', 'desc')
            ->paginate(20);

        return view('boards.show', compact('board', 'threads'));
    }

    public function showThread(Request $request, $boardName, $threadId)
    {
        $board = Board::where('name', $boardName)->where('active', true)->firstOrFail();
        $thread = Thread::where('board_id', $board->id)->findOrFail($threadId);
        
        $posts = $thread->posts()
            ->orderBy('created_at', 'asc')
            ->get();

        return view('boards.thread', compact('board', 'thread', 'posts'));
    }

    public function storeThread(Request $request, $boardName)
    {
        $board = Board::where('name', $boardName)->where('active', true)->firstOrFail();
        
        $request->validate([
            'subject' => 'nullable|string|max:200',
            'content' => 'required|string|max:8000',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,gif,webp|max:10240'
        ]);

        $imageData = null;
        if ($request->hasFile('image')) {
            $imageData = $this->handleImageUpload($request->file('image'));
        }

        $posterHash = Thread::generatePosterHash($request->ip(), 0);

        $thread = Thread::create([
            'board_id' => $board->id,
            'subject' => $request->subject,
            'content' => $request->content,
            'image_filename' => $imageData['filename'] ?? null,
            'image_original_name' => $imageData['original_name'] ?? null,
            'image_size' => $imageData['size'] ?? null,
            'ip_address' => $request->ip(),
            'poster_hash' => $posterHash,
            'bumped_at' => now()
        ]);

        // Update poster hash with actual thread ID
        $thread->update([
            'poster_hash' => Thread::generatePosterHash($request->ip(), $thread->id)
        ]);

        if ($imageData) {
            $thread->increment('image_count');
        }

        $board->incrementPostCount();

        return redirect("/{$board->name}/thread/{$thread->id}");
    }

    public function storePost(Request $request, $boardName, $threadId)
    {
        $board = Board::where('name', $boardName)->where('active', true)->firstOrFail();
        $thread = Thread::where('board_id', $board->id)->findOrFail($threadId);
        
        if ($thread->locked) {
            return back()->withErrors(['error' => 'Thread is locked']);
        }

        $request->validate([
            'content' => 'required|string|max:8000',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,gif,webp|max:10240'
        ]);

        $imageData = null;
        if ($request->hasFile('image')) {
            $imageData = $this->handleImageUpload($request->file('image'));
        }

        $posterHash = Thread::generatePosterHash($request->ip(), $thread->id);

        $post = Post::create([
            'thread_id' => $thread->id,
            'content' => $request->content,
            'image_filename' => $imageData['filename'] ?? null,
            'image_original_name' => $imageData['original_name'] ?? null,
            'image_size' => $imageData['size'] ?? null,
            'ip_address' => $request->ip(),
            'poster_hash' => $posterHash
        ]);

        $thread->addReply($post);

        return redirect("/{$board->name}/thread/{$thread->id}#post{$post->id}");
    }

    private function handleImageUpload($file)
    {
        $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();
        $originalName = $file->getClientOriginalName();
        $size = $file->getSize();

        // Store original image
        $file->storeAs('public/images', $filename);

        // Create simple thumbnail (copy for now, can enhance with image processing later)
        $file->storeAs('public/thumbs', $filename);

        return [
            'filename' => $filename,
            'original_name' => $originalName,
            'size' => $size
        ];
    }
}
EOF

# ProofOfWorkController (enhanced)
cat > app/Http/Controllers/ProofOfWorkController.php << 'EOF'
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Board;
use App\Models\Thread;
use App\Models\ProofOfWork;

class ProofOfWorkController extends Controller
{
    public function submitProof(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'hash' => 'required|string|size:64',
            'nonce' => 'required|integer|min:0',
            'data' => 'required|string',
            'pattern' => 'required|string|in:21e8,21e80,21e800,21e8000,000021e8'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false, 
                'message' => 'Invalid proof format'
            ], 422);
        }

        $verificationResult = $this->verifyProof(
            $request->input('data'),
            $request->input('nonce'),
            $request->input('hash'),
            $request->input('pattern')
        );

        if (!$verificationResult['valid']) {
            return response()->json([
                'success' => false,
                'message' => $verificationResult['error']
            ], 400);
        }

        $points = $this->calculatePoints($request->input('pattern'));

        ProofOfWork::create([
            'hash' => $request->input('hash'),
            'nonce' => $request->input('nonce'),
            'data' => $request->input('data'),
            'pattern' => $request->input('pattern'),
            'points' => $points,
            'ip_address' => $request->ip(),
            'verified_at' => now()
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Proof accepted!',
            'points' => $points,
            'total_points' => $points
        ]);
    }

    public function bumpThread(Request $request, $boardName, $threadId)
    {
        $validator = Validator::make($request->all(), [
            'hash' => 'required|string|size:64',
            'nonce' => 'required|integer|min:0',
            'data' => 'required|string',
            'pattern' => 'required|string|in:21e8,21e80,21e800'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid proof format'
            ], 422);
        }

        $board = Board::where('name', $boardName)->firstOrFail();
        $thread = Thread::where('board_id', $board->id)->findOrFail($threadId);

        $verificationResult = $this->verifyProof(
            $request->input('data'),
            $request->input('nonce'),
            $request->input('hash'),
            $request->input('pattern')
        );

        if (!$verificationResult['valid']) {
            return response()->json([
                'success' => false,
                'message' => $verificationResult['error']
            ], 400);
        }

        $points = $this->calculatePoints($request->input('pattern'));

        ProofOfWork::create([
            'thread_id' => $threadId,
            'hash' => $request->input('hash'),
            'nonce' => $request->input('nonce'),
            'data' => $request->input('data'),
            'pattern' => $request->input('pattern'),
            'points' => $points,
            'verified_at' => now(),
            'ip_address' => $request->ip()
        ]);

        $thread->increment('bump_score', $points);
        $thread->update(['bumped_at' => now()]);

        return response()->json([
            'success' => true,
            'message' => 'Thread bumped successfully',
            'points' => $points,
            'thread_bump_score' => $thread->bump_score
        ]);
    }

    private function verifyProof($data, $nonce, $submittedHash, $pattern)
    {
        $calculatedHash = hash('sha256', $data);

        if ($calculatedHash !== strtolower($submittedHash)) {
            return ['valid' => false, 'error' => 'Hash mismatch'];
        }

        if (strpos(strtolower($calculatedHash), strtolower($pattern)) === false) {
            return ['valid' => false, 'error' => 'Pattern not found'];
        }

        if (ProofOfWork::where('hash', $calculatedHash)->exists()) {
            return ['valid' => false, 'error' => 'Duplicate proof'];
        }

        return ['valid' => true];
    }

    private function calculatePoints($pattern)
    {
        $points = [
            '21e8' => 1,
            '21e80' => 5,
            '21e800' => 25,
            '21e8000' => 125,
            '000021e8' => 625
        ];
        return $points[$pattern] ?? 1;
    }

    public function getStats()
    {
        return response()->json([
            'total_proofs' => ProofOfWork::count(),
            'top_miners' => []
        ]);
    }

    public function startMiningSession(Request $request)
    {
        return response()->json(['success' => true]);
    }

    public function endMiningSession(Request $request)
    {
        return response()->json(['success' => true]);
    }
}
EOF

echo "✓ Controllers created"

# =================================================================
# 6. COPY CSS FROM PRODUCTION
# =================================================================

echo "Creating Yotsuba-style CSS..."

# Copy the production CSS
cp /dev/stdin public/css/haichan.css << 'EOF'
/* Haichan - Local Development CSS (Yotsuba-style) */
/* Color Scheme: #3D315B (purple bg), #444B6E (dark), #708B75 (medium), #9AB87A (light green) */

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

/* Header */
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

.header h1 a:hover {
    text-shadow: 2px 2px 0 #444B6E;
}

.header p {
    color: #FFFFEE;
    font-style: italic;
    margin-bottom: 8px;
}

.header nav a {
    color: #FFFFEE;
    text-decoration: none;
    margin: 0 10px;
    padding: 4px 8px;
    background: #444B6E;
    border-radius: 3px;
    font-size: 9pt;
}

.header nav a:hover {
    background: #3D315B;
}

/* Board Header */
.board-header {
    background: #9AB87A;
    padding: 15px 20px;
    border-bottom: 1px solid #444B6E;
    text-align: center;
}

.board-header h2 {
    color: #444B6E;
    font-size: 18pt;
    font-weight: bold;
    margin-bottom: 5px;
}

/* Forms */
.post-form, .reply-form {
    background: #F5F5DC;
    border: 1px solid #708B75;
    margin: 20px;
    padding: 15px;
}

.post-form h3, .reply-form h3 {
    color: #444B6E;
    font-size: 12pt;
    margin-bottom: 10px;
}

.post-form table, .reply-form table {
    width: 100%;
}

.post-form td, .reply-form td {
    padding: 5px;
    vertical-align: top;
}

.post-form input, .post-form textarea, .post-form select,
.reply-form input, .reply-form textarea, .reply-form select {
    font-family: inherit;
    font-size: 9pt;
    border: 1px solid #708B75;
    padding: 4px;
}

.post-form textarea, .reply-form textarea {
    font-family: 'Courier New', monospace;
    resize: vertical;
}

.btn-primary, .btn-bump, .btn-stop {
    background: #9AB87A;
    color: #444B6E;
    border: 1px solid #708B75;
    padding: 6px 12px;
    cursor: pointer;
    font-family: inherit;
    font-size: 9pt;
    font-weight: bold;
}

.btn-primary:hover, .btn-bump:hover {
    background: #708B75;
    color: #FFFFEE;
}

/* Thread Listing */
.threads-list {
    margin: 20px;
}

.thread-preview {
    background: #F5F5DC;
    border: 1px solid #708B75;
    margin-bottom: 15px;
    padding: 10px;
}

.subject {
    font-weight: bold;
    color: #0F0C5D;
}

.subject a {
    color: #0F0C5D;
    text-decoration: none;
}

.subject a:hover {
    color: #DD0000;
}

.poster-info {
    color: #117743;
    font-size: 9pt;
}

.thread-content {
    display: flex;
    gap: 10px;
    margin: 8px 0;
}

.thread-image {
    flex-shrink: 0;
}

.thread-text {
    flex-grow: 1;
    font-size: 9pt;
}

.thumbnail {
    max-width: 125px;
    max-height: 125px;
    border: 1px solid #708B75;
}

/* Posts */
.post {
    background: #F5F5DC;
    border: 1px solid #708B75;
    margin: 5px 20px;
    padding: 10px;
}

.op-post {
    border-color: #444B6E;
    background: #FFFACD;
    border-width: 2px;
}

.reply-post {
    margin-left: 40px;
}

.post-header {
    font-size: 10pt;
    margin-bottom: 8px;
}

.post-content {
    font-size: 10pt;
    line-height: 1.4;
}

.post-content .greentext {
    color: #789922;
}

.post-content .quote-link {
    color: #DD0000;
    text-decoration: none;
}

/* PoW Bumping */
.pow-bump-section {
    background: #F5F5DC;
    border: 1px solid #708B75;
    margin: 20px;
    padding: 15px;
}

.pow-bump-section h3 {
    color: #444B6E;
    margin-bottom: 8px;
}

.bump-mining {
    display: flex;
    align-items: center;
    gap: 10px;
    flex-wrap: wrap;
}

.mining-status {
    font-size: 8pt;
    color: #888;
    margin-top: 8px;
}

/* Board listing */
.board-listing {
    padding: 20px;
}

.boards-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 15px;
}

.board-card {
    background: #F5F5DC;
    border: 1px solid #708B75;
    padding: 15px;
}

.board-card:hover {
    border-color: #444B6E;
}

.board-card h3 a {
    color: #444B6E;
    text-decoration: none;
    font-weight: bold;
}

/* Error alerts */
.alert {
    padding: 10px;
    margin: 20px;
}

.alert-danger {
    color: #8B0000;
    background: #FFE4E1;
    border: 1px solid #CD853F;
}

/* Responsive */
@media (max-width: 768px) {
    .container {
        border: none;
    }
    
    .thread-content {
        flex-direction: column;
    }
    
    .reply-post {
        margin-left: 20px;
    }
    
    .bump-mining {
        flex-direction: column;
        align-items: stretch;
    }
}
EOF

echo "✓ CSS created"

# =================================================================
# 7. CREATE ALL VIEWS
# =================================================================

echo "Creating views..."

# Board index
cat > resources/views/boards/index.blade.php << 'EOF'
<!DOCTYPE html>
<html>
<head>
    <title>Haichan - Boards</title>
    <link rel="stylesheet" href="/css/haichan.css">
</head>
<body>
    <div class="container">
        <div class="header">
            <h1><a href="/">Haichan</a></h1>
            <p>A proof-of-work image board</p>
            <nav>
                <a href="/mining">⛏️ Mining</a>
                <a href="/boards">📋 Boards</a>
            </nav>
        </div>

        <div class="board-listing">
            <h2>Boards</h2>
            <div class="boards-grid">
                @foreach($boards as $board)
                <div class="board-card">
                    <h3><a href="{{ $board->url }}">{{ $board->title }}</a></h3>
                    <p>{{ $board->description }}</p>
                    <div class="board-stats">
                        <span>{{ $board->threads_count }} threads</span>
                        <span>{{ $board->post_count }} posts</span>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</body>
</html>
EOF

# Board show
cat > resources/views/boards/show.blade.php << 'EOF'
<!DOCTYPE html>
<html>
<head>
    <title>{{ $board->title }} - Haichan</title>
    <link rel="stylesheet" href="/css/haichan.css">
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>
<body>
    <div class="container">
        <div class="header">
            <h1><a href="/">Haichan</a></h1>
            <nav>
                <a href="/boards">📋 Boards</a>
                <a href="/mining">⛏️ Mining</a>
            </nav>
        </div>

        <div class="board-header">
            <h2>{{ $board->title }}</h2>
            <p>{{ $board->description }}</p>
        </div>

        @if($errors->any())
        <div class="alert alert-danger">
            @foreach($errors->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
        </div>
        @endif

        <div class="post-form">
            <h3>[Start a new thread]</h3>
            <form method="POST" action="/{{ $board->name }}" enctype="multipart/form-data">
                @csrf
                <table>
                    <tr>
                        <td>Subject</td>
                        <td><input type="text" name="subject" size="35" maxlength="200"></td>
                    </tr>
                    <tr>
                        <td>Comment</td>
                        <td><textarea name="content" rows="5" cols="50" required></textarea></td>
                    </tr>
                    <tr>
                        <td>File</td>
                        <td><input type="file" name="image" accept="image/*"></td>
                    </tr>
                    <tr>
                        <td></td>
                        <td><button type="submit" class="btn-primary">Submit</button></td>
                    </tr>
                </table>
            </form>
        </div>

        <div class="threads-list">
            @forelse($threads as $thread)
            <div class="thread-preview">
                <div class="thread-header">
                    <span class="subject">
                        <a href="/{{ $board->name }}/thread/{{ $thread->id }}">
                            {{ $thread->subject ?: 'No Subject' }}
                        </a>
                    </span>
                    <span class="poster-info">
                        Anonymous {{ $thread->created_at->format('m/d/y H:i') }} No.{{ $thread->id }}
                        @if($thread->bump_score > 0)
                            [PoW: {{ $thread->bump_score }}]
                        @endif
                    </span>
                </div>

                <div class="thread-content">
                    @if($thread->image_filename)
                    <div class="thread-image">
                        <img src="/storage/thumbs/{{ $thread->image_filename }}" class="thumbnail">
                    </div>
                    @endif
                    
                    <div class="thread-text">
                        <p>{!! nl2br(e(Str::limit($thread->content, 300))) !!}</p>
                    </div>
                </div>

                <div style="font-size: 8pt; color: #888; margin-top: 5px;">
                    Replies: {{ $thread->reply_count }} | 
                    Images: {{ $thread->image_count }} | 
                    Last: {{ $thread->bumped_at->diffForHumans() }}
                </div>
            </div>
            @empty
            <div class="thread-preview">
                <p style="text-align: center; padding: 40px;">No threads yet. Start the first one!</p>
            </div>
            @endforelse
        </div>

        <div style="text-align: center; padding: 20px;">
            {{ $threads->links() }}
        </div>
    </div>
</body>
</html>
EOF

# Thread view
cat > resources/views/boards/thread.blade.php << 'EOF'
<!DOCTYPE html>
<html>
<head>
    <title>{{ $thread->subject ?: 'Thread' }} - {{ $board->title }}</title>
    <link rel="stylesheet" href="/css/haichan.css">
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>
<body>
    <div class="container">
        <div class="header">
            <h1><a href="/">Haichan</a></h1>
            <nav>
                <a href="/boards">📋 Boards</a>
                <a href="/{{ $board->name }}">{{ $board->name }}/</a>
                <a href="/mining">⛏️ Mining</a>
            </nav>
        </div>

        @if($errors->any())
        <div class="alert alert-danger">
            @foreach($errors->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
        </div>
        @endif

        <!-- PoW Bump Section -->
        <div class="pow-bump-section">
            <h3>⚡ Proof of Work Bump (Score: {{ $thread->bump_score }})</h3>
            <p>Mine a hash to bump this thread!</p>
            
            <div class="bump-mining">
                <select id="bumpDifficulty">
                    <option value="21e8">21e8 (+1)</option>
                    <option value="21e80">21e80 (+5)</option>
                    <option value="21e800">21e800 (+25)</option>
                </select>
                <button id="startBumpMining" class="btn-bump">Start Mining</button>
                <button id="stopBumpMining" class="btn-stop" disabled>Stop</button>
                
                <div id="bumpMiningStatus" class="mining-status">
                    <span id="bumpHashrate">0</span> H/s | 
                    <span id="bumpHashes">0</span> hashes
                </div>
            </div>
        </div>

        <!-- Original post -->
        <div class="post op-post" id="post{{ $thread->id }}">
            <div class="post-header">
                <span class="subject">{{ $thread->subject ?: 'No Subject' }}</span>
                <span class="poster-info">
                    Anonymous {{ $thread->created_at->format('m/d/y H:i:s') }} No.{{ $thread->id }}
                </span>
            </div>
            
            @if($thread->image_filename)
            <div style="float: left; margin: 5px 15px 10px 0;">
                <div style="font-size: 8pt; margin-bottom: 3px;">
                    File: {{ $thread->image_original_name }} ({{ number_format($thread->image_size / 1024, 1) }} KB)
                </div>
                <img src="/storage/images/{{ $thread->image_filename }}" style="max-width: 200px; max-height: 200px;">
            </div>
            @endif
            
            <div class="post-content">
                {!! nl2br(e($thread->content)) !!}
            </div>
            <div style="clear: both;"></div>
        </div>

        <!-- Replies -->
        @foreach($posts as $post)
        <div class="post reply-post" id="post{{ $post->id }}">
            <div class="post-header">
                <span class="poster-info">
                    Anonymous {{ $post->created_at->format('m/d/y H:i:s') }} No.{{ $post->id }}
                </span>
            </div>
            
            @if($post->image_filename)
            <div style="float: left; margin: 5px 15px 10px 0;">
                <div style="font-size: 8pt; margin-bottom: 3px;">
                    File: {{ $post->image_original_name }}
                </div>
                <img src="/storage/images/{{ $post->image_filename }}" style="max-width: 200px; max-height: 200px;">
            </div>
            @endif
            
            <div class="post-content">
                {!! preg_replace('/&gt;&gt;(\d+)/', '<a href="#post$1" class="quote-link">&gt;&gt;$1</a>', 
                     preg_replace('/^&gt;(.+)/m', '<span class="greentext">&gt;$1</span>', 
                     nl2br(e($post->content)))) !!}
            </div>
            <div style="clear: both;"></div>
        </div>
        @endforeach

        <!-- Reply form -->
        @if(!$thread->locked)
        <div class="reply-form">
            <h3>[Post a Reply]</h3>
            <form method="POST" action="/{{ $board->name }}/thread/{{ $thread->id }}" enctype="multipart/form-data">
                @csrf
                <table>
                    <tr>
                        <td>Comment</td>
                        <td><textarea name="content" rows="5" cols="50" required></textarea></td>
                    </tr>
                    <tr>
                        <td>File</td>
                        <td><input type="file" name="image" accept="image/*"></td>
                    </tr>
                    <tr>
                        <td></td>
                        <td><button type="submit" class="btn-primary">Submit</button></td>
                    </tr>
                </table>
            </form>
        </div>
        @endif
    </div>

    <script>
        class ThreadBumper {
            constructor(threadId, boardName) {
                this.threadId = threadId;
                this.boardName = boardName;
                this.isMining = false;
                this.totalHashes = 0;
                this.nonce = Math.floor(Math.random() * 1000000);
                this.targetPattern = '21e8';
                this.startTime = null;
                
                document.getElementById('startBumpMining').onclick = () => this.startMining();
                document.getElementById('stopBumpMining').onclick = () => this.stopMining();
                document.getElementById('bumpDifficulty').onchange = (e) => {
                    this.targetPattern = e.target.value;
                };

                setInterval(() => this.updateDisplay(), 1000);
            }

            async sha256(text) {
                const encoder = new TextEncoder();
                const data = encoder.encode(text);
                const hashBuffer = await crypto.subtle.digest('SHA-256', data);
                const hashArray = Array.from(new Uint8Array(hashBuffer));
                return hashArray.map(b => b.toString(16).padStart(2, '0')).join('');
            }

            async mine() {
                const baseData = `Bump_${this.threadId}_${Date.now()}`;
                
                while (this.isMining) {
                    const data = `${baseData}_${this.nonce}`;
                    const hash = await this.sha256(data);
                    
                    this.totalHashes++;
                    this.nonce++;
                    
                    if (hash.toLowerCase().includes(this.targetPattern.toLowerCase())) {
                        await this.submitBump({
                            hash: hash,
                            nonce: this.nonce - 1,
                            data: data,
                            pattern: this.targetPattern
                        });
                        break;
                    }
                    
                    if (this.totalHashes % 1000 === 0) {
                        await new Promise(r => setTimeout(r, 1));
                    }
                }
            }

            async submitBump(proof) {
                try {
                    const response = await fetch(`/api/${this.boardName}/thread/${this.threadId}/bump`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        },
                        body: JSON.stringify(proof)
                    });
                    
                    const result = await response.json();
                    
                    if (result.success) {
                        alert(`Thread bumped! +${result.points} points`);
                        location.reload();
                    } else {
                        alert(`Failed: ${result.message}`);
                    }
                } catch (error) {
                    alert(`Error: ${error.message}`);
                }
                
                this.stopMining();
            }

            startMining() {
                this.isMining = true;
                this.startTime = Date.now();
                document.getElementById('startBumpMining').disabled = true;
                document.getElementById('stopBumpMining').disabled = false;
                this.mine();
            }

            stopMining() {
                this.isMining = false;
                document.getElementById('startBumpMining').disabled = false;
                document.getElementById('stopBumpMining').disabled = true;
            }

            updateDisplay() {
                if (this.isMining && this.startTime) {
                    const elapsed = (Date.now() - this.startTime) / 1000;
                    const hashrate = Math.round(this.totalHashes / Math.max(elapsed, 1));
                    document.getElementById('bumpHashrate').textContent = hashrate;
                    document.getElementById('bumpHashes').textContent = this.totalHashes;
                }
            }
        }

        window.addEventListener('DOMContentLoaded', () => {
            window.bumper = new ThreadBumper({{ $thread->id }}, '{{ $board->name }}');
        });

        // Quote functionality
        document.addEventListener('click', (e) => {
            if (e.target.closest('.poster-info')) {
                const post = e.target.closest('.post');
                const postId = post.id.replace('post', '');
                const textarea = document.querySelector('textarea[name="content"]');
                if (textarea) {
                    textarea.value += `>>${postId}\n`;
                    textarea.focus();
                }
            }
        });
    </script>
</body>
</html>
EOF

# Error page
cat > resources/views/errors/404.blade.php << 'EOF'
<!DOCTYPE html>
<html>
<head>
    <title>404 - Page Not Found</title>
    <link rel="stylesheet" href="/css/haichan.css">
</head>
<body>
    <div class="container">
        <div class="header">
            <h1><a href="/">Haichan</a></h1>
        </div>
        <div style="text-align: center; padding: 100px;">
            <h2 style="font-size: 48pt;">404</h2>
            <p>Board or page not found</p>
            <p><a href="/boards">Return to boards</a></p>
        </div>
    </div>
</body>
</html>
EOF

# Static pages
cat > resources/views/static/rules.blade.php << 'EOF'
<!DOCTYPE html>
<html>
<head>
    <title>Rules - Haichan</title>
    <link rel="stylesheet" href="/css/haichan.css">
</head>
<body>
    <div class="container">
        <div class="header">
            <h1><a href="/">Haichan</a></h1>
            <nav><a href="/boards">📋 Boards</a></nav>
        </div>
        <div style="padding: 20px;">
            <h2>Rules</h2>
            <ol>
                <li>No illegal content</li>
                <li>No spam or flooding</li>
                <li>Stay on topic</li>
                <li>Max 10MB images</li>
                <li>Use proof-of-work responsibly</li>
            </ol>
        </div>
    </div>
</body>
</html>
EOF

cat > resources/views/static/faq.blade.php << 'EOF'
<!DOCTYPE html>
<html>
<head>
    <title>FAQ - Haichan</title>
    <link rel="stylesheet" href="/css/haichan.css">
</head>
<body>
    <div class="container">
        <div class="header">
            <h1><a href="/">Haichan</a></h1>
            <nav><a href="/boards">📋 Boards</a></nav>
        </div>
        <div style="padding: 20px;">
            <h2>FAQ</h2>
            <h3>What is proof-of-work?</h3>
            <p>Mining SHA256 hashes to find patterns. This prevents spam and ranks threads.</p>
            
            <h3>How do I quote posts?</h3>
            <p>Use >>123456 or click on post numbers to quote them.</p>
            
            <h3>What is greentext?</h3>
            <p>Lines starting with > appear in green for quotes/stories.</p>
        </div>
    </div>
</body>
</html>
EOF

echo "✓ Views created"

# =================================================================
# 8. RUN MIGRATIONS AND SEED DATA
# =================================================================

echo "Setting up database..."

# Create storage link
php artisan storage:link

# Run migrations
php artisan migrate --force

# Seed boards
php artisan tinker --execute="
try {
App\Models\Board::create(['name' => 'gen', 'title' => '/gen/ - General', 'description' => 'General discussion and random topics']);
App\Models\Board::create(['name' => 'tech', 'title' => '/tech/ - Technology', 'description' => 'Technology, programming, and computing']);
App\Models\Board::create(['name' => 'biz', 'title' => '/biz/ - Business', 'description' => 'Business, finance, and entrepreneurship']);
App\Models\Board::create(['name' => 'film', 'title' => '/film/ - Film & TV', 'description' => 'Movies, television, and media discussion']);
App\Models\Board::create(['name' => 'x', 'title' => '/x/ - Paranormal', 'description' => 'Paranormal, conspiracy theories, and unexplained']);
App\Models\Board::create(['name' => 'lit', 'title' => '/lit/ - Literature', 'description' => 'Books, writing, and literary discussion']);
echo 'Boards created successfully!';
} catch (Exception \$e) {
echo 'Note: ' . \$e->getMessage();
}
"

# Set permissions
chmod -R 775 storage bootstrap/cache
chmod 644 public/css/haichan.css

# Clear caches
php artisan config:clear
php artisan route:clear
php artisan view:clear

echo "✓ Database and storage configured"

# =================================================================
# SETUP COMPLETE
# =================================================================

echo ""
echo "================================================================="
echo "🎉 HAICHAN LOCAL DEVELOPMENT SETUP COMPLETE!"
echo "================================================================="
echo ""
echo "🚀 START YOUR SERVER:"
echo "   php artisan serve"
echo ""
echo "🌐 THEN VISIT:"
echo "   http://localhost:8000/boards  - Board listing"
echo "   http://localhost:8000/gen     - General board"
echo "   http://localhost:8000/tech    - Technology board"
echo "   http://localhost:8000/biz     - Business board"
echo "   http://localhost:8000/film    - Film & TV board"
echo "   http://localhost:8000/x       - Paranormal board"
echo "   http://localhost:8000/lit     - Literature board"
echo "   http://localhost:8000/mining  - Mining dashboard"
echo ""
echo "✅ FEATURES READY:"
echo "   • Yotsuba-style design with your color scheme"
echo "   • 6 discussion boards (/gen/, /tech/, /biz/, /film/, /x/, /lit/)"
echo "   • Image uploads with thumbnails"
echo "   • Thread creation and replies"
echo "   • Proof-of-work thread bumping"
echo "   • Quote linking (>>123456) and greentext (>text)"
echo "   • SHA256 mining for thread ranking"
echo ""
echo "🎯 READY TO USE:"
echo "   1. Start the server: php artisan serve"
echo "   2. Visit http://localhost:8000/boards"
echo "   3. Click on any board to start posting"
echo "   4. Create threads with images"
echo "   5. Mine proof-of-work to bump threads"
echo ""
echo "Happy posting! 🎨"
echo "================================================================="
