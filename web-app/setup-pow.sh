#!/bin/bash

# haichan Proof of Work Setup Script
# This script sets up the complete mining system for your Laravel application

echo "=== haichan Proof of Work Mining Setup ==="
echo

# Check if we're in the right directory
if [ ! -f "artisan" ]; then
    echo "Error: Please run this script from your Laravel project root directory"
    exit 1
fi

# Check PHP version
PHP_VERSION=$(php -r "echo PHP_VERSION_ID;")
if [ $PHP_VERSION -lt 80100 ]; then
    echo "Error: PHP 8.1 or higher required"
    exit 1
fi

echo "✓ PHP version check passed"

# Install required Laravel packages
echo "Installing Laravel dependencies..."
composer require --no-interaction \
    pusher/pusher-php-server \
    laravel/broadcasting

echo "✓ Dependencies installed"

# Create database migrations
echo "Creating database migrations..."

# Migration: proof_of_works table
cat > database/migrations/$(date +%Y_%m_%d_%H%M%S)_create_proof_of_works_table.php << 'EOF'
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
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('thread_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('hash', 64)->unique();
            $table->bigInteger('nonce');
            $table->text('data');
            $table->string('pattern', 20);
            $table->integer('points')->default(0);
            $table->timestamp('verified_at')->nullable();
            $table->ipAddress('ip_address');
            $table->timestamps();

            $table->index(['pattern', 'created_at']);
            $table->index(['user_id', 'points']);
            $table->index(['thread_id', 'points']);
            $table->index('verified_at');
        });
    }

    public function down()
    {
        Schema::dropIfExists('proof_of_works');
    }
};
EOF

sleep 1

# Migration: add PoW fields to users
cat > database/migrations/$(date +%Y_%m_%d_%H%M%S)_add_pow_fields_to_users_table.php << 'EOF'
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->bigInteger('pow_points')->default(0)->after('email');
            $table->timestamp('last_mining_activity')->nullable()->after('pow_points');
            $table->string('bitcoin_address', 62)->nullable()->unique()->after('last_mining_activity');
            $table->boolean('mining_enabled')->default(true)->after('bitcoin_address');
            
            $table->index(['pow_points', 'created_at']);
            $table->index('bitcoin_address');
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['pow_points', 'created_at']);
            $table->dropIndex(['bitcoin_address']);
            $table->dropColumn(['pow_points', 'last_mining_activity', 'bitcoin_address', 'mining_enabled']);
        });
    }
};
EOF

sleep 1

# Migration: mining sessions
cat > database/migrations/$(date +%Y_%m_%d_%H%M%S)_create_mining_sessions_table.php << 'EOF'
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('mining_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade');
            $table->ipAddress('ip_address');
            $table->string('user_agent')->nullable();
            $table->integer('hashes_computed')->default(0);
            $table->integer('valid_proofs')->default(0);
            $table->integer('points_earned')->default(0);
            $table->timestamp('started_at');
            $table->timestamp('last_activity')->nullable();
            $table->timestamp('ended_at')->nullable();
            $table->boolean('active')->default(true);
            $table->timestamps();

            $table->index(['user_id', 'active']);
            $table->index(['ip_address', 'started_at']);
            $table->index('last_activity');
        });
    }

    public function down()
    {
        Schema::dropIfExists('mining_sessions');
    }
};
EOF

echo "✓ Database migrations created"

# Create models directory if it doesn't exist
mkdir -p app/Models

# Create ProofOfWork model
cat > app/Models/ProofOfWork.php << 'EOF'
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProofOfWork extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'thread_id', 'hash', 'nonce', 'data', 
        'pattern', 'points', 'verified_at', 'ip_address'
    ];

    protected $casts = [
        'nonce' => 'integer',
        'points' => 'integer', 
        'verified_at' => 'datetime'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function thread(): BelongsTo
    {
        return $this->belongsTo(Thread::class);
    }

    public function scopeByPattern($query, $pattern)
    {
        return $query->where('pattern', $pattern);
    }

    public function scopeRecent($query, $hours = 24)
    {
        return $query->where('created_at', '>=', now()->subHours($hours));
    }

    public function getDifficultyAttribute()
    {
        $difficulties = [
            '21e8' => 'Easy',
            '21e80' => 'Medium',
            '21e800' => 'Hard', 
            '21e8000' => 'Extreme',
            '000021e8' => 'Insane'
        ];
        return $difficulties[$this->pattern] ?? 'Unknown';
    }
}
EOF

# Create MiningSession model
cat > app/Models/MiningSession.php << 'EOF'
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MiningSession extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'ip_address', 'user_agent', 'hashes_computed',
        'valid_proofs', 'points_earned', 'started_at', 'last_activity',
        'ended_at', 'active'
    ];

    protected $casts = [
        'hashes_computed' => 'integer',
        'valid_proofs' => 'integer',
        'points_earned' => 'integer',
        'active' => 'boolean',
        'started_at' => 'datetime',
        'last_activity' => 'datetime', 
        'ended_at' => 'datetime'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getDurationAttribute()
    {
        $end = $this->ended_at ?? $this->last_activity ?? now();
        return $this->started_at->diffInSeconds($end);
    }

    public function getHashrateAttribute()
    {
        $duration = $this->duration;
        return $duration > 0 ? round($this->hashes_computed / $duration, 2) : 0;
    }

    public function updateActivity($hashesComputed = 0, $validProofs = 0, $pointsEarned = 0)
    {
        $this->update([
            'hashes_computed' => $this->hashes_computed + $hashesComputed,
            'valid_proofs' => $this->valid_proofs + $validProofs,
            'points_earned' => $this->points_earned + $pointsEarned,
            'last_activity' => now()
        ]);
    }

    public function endSession()
    {
        $this->update(['active' => false, 'ended_at' => now()]);
    }
}
EOF

echo "✓ Eloquent models created"

# Create controller
mkdir -p app/Http/Controllers
cat > app/Http/Controllers/ProofOfWorkController.php << 'EOF'
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use App\Models\ProofOfWork;
use App\Models\MiningSession;

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
            return response()->json(['success' => false, 'message' => 'Invalid proof format'], 422);
        }

        $verificationResult = $this->verifyProof(
            $request->input('data'),
            $request->input('nonce'), 
            $request->input('hash'),
            $request->input('pattern')
        );

        if (!$verificationResult['valid']) {
            return response()->json(['success' => false, 'message' => $verificationResult['error']], 400);
        }

        $points = $this->calculatePoints($request->input('pattern'));

        $proof = ProofOfWork::create([
            'user_id' => auth()->id() ?? null,
            'hash' => $request->input('hash'),
            'nonce' => $request->input('nonce'),
            'data' => $request->input('data'),
            'pattern' => $request->input('pattern'),
            'points' => $points,
            'verified_at' => now(),
            'ip_address' => $request->ip()
        ]);

        if (auth()->check()) {
            auth()->user()->increment('pow_points', $points);
        }

        return response()->json([
            'success' => true,
            'points' => $points,
            'total_points' => auth()->user()->pow_points ?? 0
        ]);
    }

    private function verifyProof($data, $nonce, $submittedHash, $pattern)
    {
        $calculatedHash = hash('sha256', $data);

        if ($calculatedHash !== strtolower($submittedHash)) {
            return ['valid' => false, 'error' => 'Hash mismatch'];
        }

        if (!$this->hashMatchesPattern($calculatedHash, $pattern)) {
            return ['valid' => false, 'error' => 'Pattern not found'];
        }

        if (ProofOfWork::where('hash', $calculatedHash)->exists()) {
            return ['valid' => false, 'error' => 'Duplicate proof'];
        }

        return ['valid' => true];
    }

    private function hashMatchesPattern($hash, $pattern)
    {
        return strpos(strtolower($hash), strtolower($pattern)) !== false;
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
            'top_miners' => User::where('pow_points', '>', 0)
                ->orderBy('pow_points', 'desc')
                ->limit(10)
                ->get(['username', 'pow_points'])
        ]);
    }

    public function startMiningSession(Request $request)
    {
        if (auth()->check()) {
            auth()->user()->miningSessions()->where('active', true)->update([
                'active' => false, 'ended_at' => now()
            ]);

            $session = auth()->user()->miningSessions()->create([
                'ip_address' => $request->ip(),
                'user_agent' => $request->header('User-Agent'),
                'started_at' => now(),
                'last_activity' => now()
            ]);

            return response()->json(['success' => true, 'session_id' => $session->id]);
        }

        return response()->json(['success' => false], 401);
    }

    public function endMiningSession(Request $request)
    {
        if (auth()->check()) {
            auth()->user()->miningSessions()
                ->where('active', true)
                ->update(['active' => false, 'ended_at' => now()]);
        }
        return response()->json(['success' => true]);
    }
}
EOF

echo "✓ Controller created"

# Create views directory
mkdir -p resources/views/mining

# Create mining dashboard view  
cat > resources/views/mining/dashboard.blade.php << 'EOF'
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>haichan Mining Dashboard</title>
    <style>
        body { 
            font-family: 'Monaco', monospace; 
            background: #0a0a0a; 
            color: #00ff00; 
            margin: 0; 
            padding: 20px; 
        }
        .mining-container { max-width: 1200px; margin: 0 auto; }
        .header { text-align: center; margin-bottom: 40px; padding: 20px; border: 1px solid #333; background: #111; }
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 30px; }
        .stat-card { background: #111; border: 1px solid #333; padding: 20px; text-align: center; }
        .stat-value { font-size: 2em; font-weight: bold; margin-bottom: 10px; }
        .mining-controls { background: #111; border: 1px solid #333; padding: 30px; margin-bottom: 30px; }
        .control-group { margin-bottom: 20px; }
        button { background: #333; color: #00ff00; border: 1px solid #555; padding: 12px 24px; cursor: pointer; margin-right: 10px; }
        button:hover { background: #444; }
        button:disabled { opacity: 0.5; cursor: not-allowed; }
        select { background: #222; color: #00ff00; border: 1px solid #555; padding: 10px; }
        .mining-log { height: 300px; overflow-y: auto; background: #000; padding: 15px; font-size: 0.8em; }
        .log-success { color: #ffff00; }
        .log-info { color: #00ffff; }
        .log-error { color: #ff0000; }
    </style>
</head>
<body>
    <div class="mining-container">
        <div class="header">
            <h1>haichan Proof of Work Mining</h1>
            <p>Ultimate computational intensity for 256 elite miners</p>
        </div>

        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-value" id="hashrate">0</div>
                <div>Hashes/sec</div>
            </div>
            <div class="stat-card">
                <div class="stat-value" id="totalHashes">0</div>
                <div>Total Hashes</div>
            </div>
            <div class="stat-card">
                <div class="stat-value" id="validProofs">0</div>
                <div>Valid Proofs</div>
            </div>
            <div class="stat-card">
                <div class="stat-value" id="totalPoints">{{ auth()->user()->pow_points ?? 0 }}</div>
                <div>Your Points</div>
            </div>
        </div>

        <div class="mining-controls">
            <div class="control-group">
                <label>Difficulty Pattern:</label>
                <select id="difficultySelect">
                    <option value="21e8">21e8 (Easy - 1 point)</option>
                    <option value="21e80">21e80 (Medium - 5 points)</option>
                    <option value="21e800">21e800 (Hard - 25 points)</option>
                    <option value="21e8000">21e8000 (Extreme - 125 points)</option>
                    <option value="000021e8">000021e8 (Insane - 625 points)</option>
                </select>
            </div>
            
            <button id="startMining">Start Mining</button>
            <button id="stopMining" disabled>Stop Mining</button>
            <button id="clearLog">Clear Log</button>
        </div>

        <div class="mining-log" id="miningLog">
            <div class="log-info">[SYSTEM] Mining engine ready. Select difficulty and start mining.</div>
        </div>
    </div>

    <script>
        class HaichanMiner {
            constructor() {
                this.isMining = false;
                this.totalHashes = 0;
                this.validProofs = 0;
                this.nonce = 0;
                this.targetPattern = '21e8';
                this.startTime = 0;
                
                document.getElementById('startMining').addEventListener('click', () => this.startMining());
                document.getElementById('stopMining').addEventListener('click', () => this.stopMining());
                document.getElementById('clearLog').addEventListener('click', () => this.clearLog());
                document.getElementById('difficultySelect').addEventListener('change', (e) => {
                    this.targetPattern = e.target.value;
                });

                setInterval(() => this.updateHashrate(), 1000);
            }

            async sha256(text) {
                const encoder = new TextEncoder();
                const data = encoder.encode(text);
                const hashBuffer = await crypto.subtle.digest('SHA-256', data);
                const hashArray = Array.from(new Uint8Array(hashBuffer));
                return hashArray.map(b => b.toString(16).padStart(2, '0')).join('');
            }

            async mine() {
                const baseData = `haichan_${Date.now()}_${Math.random()}`;
                
                while (this.isMining) {
                    const data = `${baseData}_${this.nonce}`;
                    const hash = await this.sha256(data);
                    
                    this.totalHashes++;
                    this.nonce++;
                    
                    if (this.totalHashes % 100 === 0) {
                        document.getElementById('totalHashes').textContent = this.totalHashes.toLocaleString();
                    }
                    
                    if (this.isValidProof(hash)) {
                        this.validProofs++;
                        document.getElementById('validProofs').textContent = this.validProofs;
                        this.logSuccess(`VALID PROOF FOUND! Hash: ${hash}`);
                        
                        await this.submitProof({ hash, nonce: this.nonce - 1, data, pattern: this.targetPattern });
                    }
                    
                    if (this.totalHashes % 1000 === 0) {
                        await new Promise(resolve => setTimeout(resolve, 1));
                    }
                }
            }

            isValidProof(hash) {
                return hash.toLowerCase().includes(this.targetPattern.toLowerCase());
            }

            async submitProof(proof) {
                try {
                    const response = await fetch('/api/submit-proof', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: JSON.stringify(proof)
                    });
                    
                    const result = await response.json();
                    
                    if (result.success) {
                        this.logSuccess(`Proof accepted! +${result.points} points (Total: ${result.total_points})`);
                        document.getElementById('totalPoints').textContent = result.total_points;
                    } else {
                        this.logError(`Proof rejected: ${result.message}`);
                    }
                } catch (error) {
                    this.logError(`Submit failed: ${error.message}`);
                }
            }

            startMining() {
                if (this.isMining) return;
                
                this.isMining = true;
                this.startTime = Date.now();
                
                document.getElementById('startMining').disabled = true;
                document.getElementById('stopMining').disabled = false;
                
                this.logInfo(`Mining started with pattern: ${this.targetPattern}`);
                this.mine();
            }

            stopMining() {
                this.isMining = false;
                
                document.getElementById('startMining').disabled = false;
                document.getElementById('stopMining').disabled = true;
                
                this.logInfo('Mining stopped');
            }

            updateHashrate() {
                if (this.isMining && this.startTime) {
                    const elapsed = (Date.now() - this.startTime) / 1000;
                    const hashrate = Math.round(this.totalHashes / Math.max(elapsed, 1));
                    document.getElementById('hashrate').textContent = hashrate.toLocaleString();
                }
            }

            logInfo(message) { this.addLogEntry(message, 'log-info'); }
            logSuccess(message) { this.addLogEntry(message, 'log-success'); }
            logError(message) { this.addLogEntry(message, 'log-error'); }

            addLogEntry(message, className) {
                const log = document.getElementById('miningLog');
                const entry = document.createElement('div');
                entry.className = className;
                entry.textContent = `[${new Date().toLocaleTimeString()}] ${message}`;
                log.appendChild(entry);
                log.scrollTop = log.scrollHeight;
            }

            clearLog() {
                document.getElementById('miningLog').innerHTML = '';
                this.logInfo('Mining log cleared');
            }
        }

        window.addEventListener('DOMContentLoaded', () => {
            window.haichanMiner = new HaichanMiner();
        });
    </script>
</body>
</html>
EOF

echo "✓ Mining dashboard view created"

# Add routes to web.php
cat >> routes/web.php << 'EOF'

// haichan Proof of Work Routes
Route::get('/mining', function() {
    return view('mining.dashboard');
})->name('mining.dashboard');

Route::prefix('api')->group(function() {
    Route::post('/submit-proof', [App\Http\Controllers\ProofOfWorkController::class, 'submitProof']);
    Route::get('/mining-stats', [App\Http\Controllers\ProofOfWorkController::class, 'getStats']);
    Route::post('/start-mining-session', [App\Http\Controllers\ProofOfWorkController::class, 'startMiningSession']);
    Route::post('/end-mining-session', [App\Http\Controllers\ProofOfWorkController::class, 'endMiningSession']);
});
EOF

echo "✓ Routes added"

# Run migrations
echo "Running database migrations..."
php artisan migrate --force

echo "✓ Database migrations completed"

# Clear Laravel cache
echo "Clearing Laravel cache..."
php artisan config:clear
php artisan route:clear  
php artisan view:clear
php artisan cache:clear

echo "✓ Cache cleared"

# Set permissions
echo "Setting permissions..."
chmod -R 755 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache 2>/dev/null || echo "Note: Could not set ownership (run as root if needed)"

echo "✓ Permissions set"

echo
echo "=== haichan Proof of Work Mining System Setup Complete! ==="
echo
echo "🚀 NEXT STEPS:"
echo "1. Start your Laravel development server:"
echo "   php artisan serve"
echo
echo "2. Visit the mining dashboard:"
echo "   http://localhost:8000/mining"
echo  
echo "3. Start mining with different difficulty patterns:"
echo "   - 21e8: Easy (1 point)"
echo "   - 21e80: Medium (5 points)" 
echo "   - 21e800: Hard (25 points)"
echo "   - 21e8000: Extreme (125 points)"
echo "   - 000021e8: Insane (625 points)"
echo
echo "🔥 MINING FEATURES:"
echo "✓ Real-time SHA256 mining in browser"
echo "✓ Multiple difficulty levels with exponential rewards"
echo "✓ Proof verification and anti-cheat protection"
echo "✓ Mining session tracking and statistics"
echo "✓ Leaderboard and ranking system"
echo "✓ Thread bumping with PoW (when integrated)"
echo
echo "⚡ PERFORMANCE TIPS:"
echo "- Close other browser tabs for maximum hashrate"
echo "- Use Chrome/Edge for best WebAssembly performance"  
echo "- Start with '21e8' pattern to test the system"
echo "- Higher difficulty = exponentially more rewards"
echo
echo "Happy mining! 🎯"
