<?php

namespace App\Console\Commands;

use App\Models\ChatRoom;
use Illuminate\Console\Command;

class InitializeChatRooms extends Command
{
    protected $signature = 'chat:init';
    protected $description = 'Initialize default chat rooms for PoW chat system';

    public function handle()
    {
        $this->info('🚀 Initializing PoW Chat Rooms...');

        $defaultRooms = [
            [
                'name' => 'General Chat',
                'slug' => 'general',
                'description' => 'Main chat room for general discussion. Low PoW requirement - perfect for newcomers.',
                'pow_difficulty' => '21e8',
                'min_pow_points' => 1,
                'message_rate_limit' => 10,
                'max_users' => 100,
            ],
            [
                'name' => 'Mining Masters',
                'slug' => 'mining-masters',  
                'description' => 'Elite chat for serious miners. Higher PoW requirements and technical discussion.',
                'pow_difficulty' => '21e',
                'min_pow_points' => 100,
                'message_rate_limit' => 5,
                'max_users' => 50,
            ],
            [
                'name' => 'Hash Legends',
                'slug' => 'hash-legends',
                'description' => 'Exclusive room for top hashers. Prove your worth with legendary patterns!',
                'pow_difficulty' => '777',
                'min_pow_points' => 1000,
                'message_rate_limit' => 3,
                'max_users' => 25,
            ],
            [
                'name' => 'Crypto Corner',
                'slug' => 'crypto-corner',
                'description' => 'Deep technical discussions about cryptography, Bitcoin, and blockchain tech.',
                'pow_difficulty' => '21e',
                'min_pow_points' => 50,
                'message_rate_limit' => 8,
                'max_users' => 75,
            ],
            [
                'name' => 'Rare Collectors',
                'slug' => 'rare-collectors',
                'description' => 'For those who seek deadbeef, 1337, and other legendary patterns. Elite miners only.',
                'pow_difficulty' => '1337',
                'min_pow_points' => 2500,
                'message_rate_limit' => 2,
                'max_users' => 15,
            ]
        ];

        foreach ($defaultRooms as $roomData) {
            $room = ChatRoom::firstOrCreate(
                ['slug' => $roomData['slug']], 
                $roomData
            );

            if ($room->wasRecentlyCreated) {
                $this->info("✅ Created room: {$room->name} ({$room->slug})");
            } else {
                $this->info("ℹ️  Room already exists: {$room->name} ({$room->slug})");
            }
        }

        $totalRooms = ChatRoom::count();
        
        $this->info('');
        $this->info("🎉 Chat room initialization complete!");
        $this->info("📊 Total rooms: {$totalRooms}");
        $this->info('');
        $this->info('Room Access Requirements:');
        $this->table(
            ['Room', 'Difficulty', 'Min PoW Points', 'Rate Limit'],
            ChatRoom::all()->map(function ($room) {
                return [
                    $room->name,
                    $room->pow_difficulty,
                    number_format($room->min_pow_points),
                    "{$room->message_rate_limit}/min"
                ];
            })->toArray()
        );

        $this->info('');
        $this->info('💬 Users can now access PoW Chat at /chat');
        $this->info('⚡ Each message requires proof-of-work mining!');

        return 0;
    }
}