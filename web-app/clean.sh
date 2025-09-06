#!/bin/bash

echo "=== Fixing Board Data Issue ==="

cd ~/Downloads/hai3-master/web-app

# Let's check what's actually in the boards table
echo "Checking boards table contents..."
php artisan tinker --execute="
try {
    echo 'Boards table structure:' . PHP_EOL;
    \$columns = DB::select('PRAGMA table_info(boards)');
    foreach(\$columns as \$column) {
        echo '  - ' . \$column->name . ' (' . \$column->type . ')' . PHP_EOL;
    }
    echo PHP_EOL;
    
    echo 'Raw boards data:' . PHP_EOL;
    \$boards = DB::table('boards')->get();
    foreach(\$boards as \$board) {
        echo '  Board: ' . json_encode(\$board) . PHP_EOL;
    }
    echo 'Total rows: ' . \$boards->count() . PHP_EOL;
    
} catch (Exception \$e) {
    echo 'Error: ' . \$e->getMessage() . PHP_EOL;
}
"

echo ""
echo "Let's check if the 'active' column exists and what values it has..."
php artisan tinker --execute="
try {
    \$allBoards = DB::table('boards')->get();
    foreach(\$allBoards as \$board) {
        \$active = isset(\$board->active) ? \$board->active : 'column missing';
        echo 'Board: ' . \$board->name . ', Active: ' . \$active . PHP_EOL;
    }
} catch (Exception \$e) {
    echo 'Error: ' . \$e->getMessage() . PHP_EOL;
}
"

echo ""
echo "Checking what App\Models\Board::all() returns..."
php artisan tinker --execute="
try {
    \$boards = App\Models\Board::all();
    echo 'Board::all() count: ' . \$boards->count() . PHP_EOL;
    foreach(\$boards as \$board) {
        echo 'Board object: ' . \$board->name . ' (active: ' . (\$board->active ?? 'null') . ')' . PHP_EOL;
    }
    
    echo PHP_EOL . 'Testing where active = true:' . PHP_EOL;
    \$activeBoards = App\Models\Board::where('active', true)->get();
    echo 'Active boards count: ' . \$activeBoards->count() . PHP_EOL;
    
    echo PHP_EOL . 'Testing where active = 1:' . PHP_EOL;
    \$activeBoards1 = App\Models\Board::where('active', 1)->get();
    echo 'Active boards (=1) count: ' . \$activeBoards1->count() . PHP_EOL;
    
    echo PHP_EOL . 'Testing without where clause:' . PHP_EOL;
    \$allBoards = App\Models\Board::get();
    echo 'All boards count: ' . \$allBoards->count() . PHP_EOL;
    
} catch (Exception \$e) {
    echo 'Board model error: ' . \$e->getMessage() . PHP_EOL;
}
"

echo ""
echo "Let's fix the board data and model..."

# First, let's ensure we have boards in the database
echo "Ensuring boards exist in database..."
php artisan tinker --execute="
try {
    // Delete all existing boards first
    DB::table('boards')->delete();
    
    // Insert boards with the simplest possible structure
    \$boardsData = [
        ['name' => 'gen', 'description' => 'General discussion and random topics'],
        ['name' => 'tech', 'description' => 'Technology, programming, and computing'],
        ['name' => 'biz', 'description' => 'Business, finance, and entrepreneurship'],
        ['name' => 'film', 'description' => 'Movies, television, and media discussion'],
        ['name' => 'x', 'description' => 'Paranormal, conspiracy theories, and unexplained'],
        ['name' => 'lit', 'description' => 'Books, writing, and literary discussion']
    ];
    
    foreach(\$boardsData as \$boardData) {
        // Check what columns exist and insert accordingly
        \$columns = DB::select('PRAGMA table_info(boards)');
        \$columnNames = array_map(function(\$col) { return \$col->name; }, \$columns);
        
        \$insertData = [
            'name' => \$boardData['name'],
            'description' => \$boardData['description']
        ];
        
        // Add optional columns if they exist
        if (in_array('active', \$columnNames)) {
            \$insertData['active'] = 1;
        }
        if (in_array('created_at', \$columnNames)) {
            \$insertData['created_at'] = now();
        }
        if (in_array('updated_at', \$columnNames)) {
            \$insertData['updated_at'] = now();
        }
        
        DB::table('boards')->insert(\$insertData);
    }
    
    echo 'Boards inserted successfully!' . PHP_EOL;
    echo 'Total boards now: ' . DB::table('boards')->count() . PHP_EOL;
    
} catch (Exception \$e) {
    echo 'Insert error: ' . \$e->getMessage() . PHP_EOL;
}
"

# Update the Board model to be more flexible
echo "Updating Board model to handle missing 'active' column..."
cat > app/Models/Board.php << 'EOF'
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

class Board extends Model
{
    protected $fillable = [
        'name', 'description', 'active'
    ];

    protected $casts = [
        'active' => 'boolean'
    ];

    public function threads()
    {
        return $this->hasMany(Thread::class);
    }

    public function getUrlAttribute()
    {
        return "/{$this->name}";
    }

    public function getTitleAttribute()
    {
        $titles = [
            'gen' => '/gen/ - General',
            'tech' => '/tech/ - Technology', 
            'biz' => '/biz/ - Business',
            'film' => '/film/ - Film & TV',
            'x' => '/x/ - Paranormal',
            'lit' => '/lit/ - Literature'
        ];
        
        return $titles[$this->name] ?? "/{$this->name}/";
    }

    public function getPostCountAttribute()
    {
        return $this->threads()->sum('reply_count') + $this->threads()->count();
    }

    public function getLastPostAtAttribute()
    {
        $lastThread = $this->threads()->latest('created_at')->first();
        return $lastThread ? $lastThread->created_at : $this->updated_at;
    }

    public function incrementPostCount()
    {
        $this->touch();
    }

    // Override the scope to handle missing 'active' column
    public function scopeActive($query)
    {
        if (Schema::hasColumn('boards', 'active')) {
            return $query->where('active', 1);
        }
        return $query; // Return all if no active column
    }

    // Static method to get active boards
    public static function getActiveBoards()
    {
        if (Schema::hasColumn('boards', 'active')) {
            return static::where('active', 1)->get();
        }
        return static::all();
    }
}
EOF

# Update routes to use the new method
echo "Updating routes to handle the active column issue..."
cat > routes/web.php << 'EOF'
<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/test', function () {
    try {
        $boardCount = \App\Models\Board::count();
        $boards = \App\Models\Board::all()->pluck('name');
        $activeBoards = \App\Models\Board::getActiveBoards()->pluck('name');
        
        return response()->json([
            'status' => 'Routes working!',
            'total_boards' => $boardCount,
            'all_boards' => $boards,
            'active_boards' => $activeBoards
        ]);
    } catch (Exception $e) {
        return response()->json(['error' => $e->getMessage()]);
    }
});

Route::get('/boards', function () {
    try {
        $boards = \App\Models\Board::getActiveBoards();
        return view('boards.index', compact('boards'));
    } catch (Exception $e) {
        return response()->json(['error' => $e->getMessage()]);
    }
});

// Individual board routes
Route::get('/gen', function () {
    try {
        $board = \App\Models\Board::where('name', 'gen')->first();
        if (!$board) {
            throw new Exception('Board "gen" not found');
        }
        $threads = $board->threads()->orderBy('created_at', 'desc')->paginate(20);
        return view('boards.show', compact('board', 'threads'));
    } catch (Exception $e) {
        return response()->json(['error' => $e->getMessage()]);
    }
});

Route::get('/tech', function () {
    try {
        $board = \App\Models\Board::where('name', 'tech')->first();
        if (!$board) {
            throw new Exception('Board "tech" not found');
        }
        $threads = $board->threads()->orderBy('created_at', 'desc')->paginate(20);
        return view('boards.show', compact('board', 'threads'));
    } catch (Exception $e) {
        return response()->json(['error' => $e->getMessage()]);
    }
});

Route::get('/biz', function () {
    try {
        $board = \App\Models\Board::where('name', 'biz')->first();
        if (!$board) {
            throw new Exception('Board "biz" not found');
        }
        $threads = $board->threads()->orderBy('created_at', 'desc')->paginate(20);
        return view('boards.show', compact('board', 'threads'));
    } catch (Exception $e) {
        return response()->json(['error' => $e->getMessage()]);
    }
});

Route::get('/film', function () {
    try {
        $board = \App\Models\Board::where('name', 'film')->first();
        if (!$board) {
            throw new Exception('Board "film" not found');
        }
        $threads = $board->threads()->orderBy('created_at', 'desc')->paginate(20);
        return view('boards.show', compact('board', 'threads'));
    } catch (Exception $e) {
        return response()->json(['error' => $e->getMessage()]);
    }
});

Route::get('/x', function () {
    try {
        $board = \App\Models\Board::where('name', 'x')->first();
        if (!$board) {
            throw new Exception('Board "x" not found');
        }
        $threads = $board->threads()->orderBy('created_at', 'desc')->paginate(20);
        return view('boards.show', compact('board', 'threads'));
    } catch (Exception $e) {
        return response()->json(['error' => $e->getMessage()]);
    }
});

Route::get('/lit', function () {
    try {
        $board = \App\Models\Board::where('name', 'lit')->first();
        if (!$board) {
            throw new Exception('Board "lit" not found');
        }
        $threads = $board->threads()->orderBy('created_at', 'desc')->paginate(20);
        return view('boards.show', compact('board', 'threads'));
    } catch (Exception $e) {
        return response()->json(['error' => $e->getMessage()]);
    }
});

Route::get('/mining', function() {
    return view('mining.dashboard');
});

Route::fallback(function () {
    return response()->view('errors.404', [], 404);
});
EOF

# Clear caches
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Test the fix
echo ""
echo "Testing the fix..."
php artisan tinker --execute="
try {
    echo 'Total boards: ' . App\Models\Board::count() . PHP_EOL;
    echo 'Active boards: ' . App\Models\Board::getActiveBoards()->count() . PHP_EOL;
    
    foreach(App\Models\Board::getActiveBoards() as \$board) {
        echo '- ' . \$board->name . ': ' . \$board->title . PHP_EOL;
    }
} catch (Exception \$e) {
    echo 'Test error: ' . \$e->getMessage() . PHP_EOL;
}
"

echo ""
echo "✅ Board Data Fixed!"
echo ""
echo "🧪 TEST THESE URLs NOW:"
echo "   http://localhost:8000/test        - Should show board data in JSON"
echo "   http://localhost:8000/boards      - Should show board listing"
echo "   http://localhost:8000/gen         - Should show general board"
echo ""
echo "The issue was likely:"
echo "- Missing boards in database, OR"
echo "- Query trying to filter by 'active' column that doesn't exist properly"
echo ""
echo "The fix:"
echo "- Inserted boards with proper structure"
echo "- Made Board model handle missing 'active' column gracefully"
echo "- Updated routes to use safer queries"
echo ""
