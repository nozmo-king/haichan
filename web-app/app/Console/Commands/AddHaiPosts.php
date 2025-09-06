<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Board;
use App\Models\Thread;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;

class AddHaiPosts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:add-hai-posts {--image-path= : Path to image file to attach}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Add "hai" posts with "chan" content to all boards';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $imagePath = $this->option('image-path');
        $imageStoragePath = null;
        $imageFilename = null;

        // Handle image attachment if provided
        if ($imagePath && file_exists($imagePath)) {
            $filename = 'hai_chan_' . time() . '.' . pathinfo($imagePath, PATHINFO_EXTENSION);
            $imageStoragePath = 'images/' . $filename;
            $imageFilename = $filename;
            
            // Copy image directly to public storage directory
            $publicPath = storage_path('app/public/images/') . $filename;
            copy($imagePath, $publicPath);
            $this->info("Image copied to storage: {$publicPath}");
        }

        // Get all boards
        $boards = Board::all();
        
        if ($boards->isEmpty()) {
            $this->error('No boards found. Run the board seeder first.');
            return 1;
        }

        // Create a test user or use first available
        $user = User::first();
        if (!$user) {
            $this->info('No users found, creating threads without user association.');
        }

        $createdCount = 0;
        
        foreach ($boards as $board) {
            // Generate proof of work data for the thread
            $challengeId = bin2hex(random_bytes(16));
            $data = "thread:{$board->code}:hai:{$challengeId}";
            
            // Create a simple valid PoW (just use nonce 1 for simplicity)
            $nonce = 1;
            $hash = hash('sha256', $data . ':' . $nonce);
            
            // Create thread
            $thread = Thread::create([
                'board_id' => $board->id,
                'title' => 'hai',
                'content' => 'chan',
                'user_id' => $user ? $user->id : null,
                'author_name' => 'Anonymous',
                'image_path' => $imageStoragePath,
                'image_filename' => $imageFilename,
                'pow_nonce' => $nonce,
                'pow_hash' => $hash,
                'pow_challenge_id' => $challengeId,
                'pow_pattern' => '21e8',
                'pow_difficulty' => 1.0,
                'pow_verified_at' => now(),
                'bumped_at' => now()
            ]);

            $this->info("Created thread #{$thread->id} 'hai' on board /{$board->code}/");
            $createdCount++;
        }

        $this->info("Successfully created {$createdCount} 'hai' threads across all boards.");
        
        if ($imageStoragePath) {
            $this->info("All threads include the attached image: {$imageFilename}");
        }
        
        return 0;
    }
}
