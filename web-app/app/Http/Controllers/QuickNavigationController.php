<?php

namespace App\Http\Controllers;

use App\Models\Board;
use App\Models\Thread;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class QuickNavigationController extends Controller
{
    /**
     * Quick search for threads and boards
     */
    public function quickSearch(Request $request)
    {
        $query = $request->input('query');
        if (empty($query) || strlen($query) < 2) {
            return response()->json(['results' => []]);
        }

        $results = [];

        // Search threads
        $threads = Thread::with('board')
            ->where(function($q) use ($query) {
                $q->where('subject', 'ILIKE', "%{$query}%")
                  ->orWhere('content', 'ILIKE', "%{$query}%");
            })
            ->orderBy('bumped_at', 'desc')
            ->limit(8)
            ->get();

        foreach ($threads as $thread) {
            $results[] = [
                'type' => 'thread',
                'id' => $thread->id,
                'subject' => $thread->subject ?: 'No Subject',
                'board' => $thread->board->code,
                'reply_count' => $thread->reply_count,
                'updated_at' => $thread->bumped_at,
            ];
        }

        // Search boards
        $boards = Board::where('active', true)
            ->where(function($q) use ($query) {
                $q->where('name', 'ILIKE', "%{$query}%")
                  ->orWhere('code', 'ILIKE', "%{$query}%")
                  ->orWhere('description', 'ILIKE', "%{$query}%");
            })
            ->limit(4)
            ->get();

        foreach ($boards as $board) {
            $results[] = [
                'type' => 'board',
                'id' => $board->id,
                'name' => $board->name,
                'code' => $board->code,
                'description' => $board->description,
            ];
        }

        // Sort results by relevance
        usort($results, function($a, $b) use ($query) {
            $aScore = $this->calculateRelevance($a, $query);
            $bScore = $this->calculateRelevance($b, $query);
            return $bScore <=> $aScore;
        });

        return response()->json([
            'results' => array_slice($results, 0, 12)
        ]);
    }

    /**
     * Get thread URL by ID
     */
    public function getThreadUrl($threadId)
    {
        $thread = Thread::with('board')->find($threadId);
        
        if (!$thread) {
            return response()->json(['error' => 'Thread not found'], 404);
        }

        return response()->json([
            'url' => "/boards/{$thread->board->name}/thread/{$thread->id}",
            'thread' => [
                'id' => $thread->id,
                'subject' => $thread->subject,
                'board' => $thread->board->name,
            ]
        ]);
    }

    /**
     * Get recent threads
     */
    public function getRecentThreads()
    {
        $threads = Thread::with('board')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get()
            ->map(function($thread) {
                return [
                    'id' => $thread->id,
                    'subject' => $thread->subject ?: 'No Subject',
                    'board' => $thread->board->code,
                    'reply_count' => $thread->reply_count,
                    'updated_at' => $thread->bumped_at,
                ];
            });

        return response()->json(['threads' => $threads]);
    }

    /**
     * Get active threads (by replies/activity)
     */
    public function getActiveThreads()
    {
        $threads = Thread::with('board')
            ->where('bumped_at', '>=', now()->subDays(7))
            ->orderBy('reply_count', 'desc')
            ->orderBy('bumped_at', 'desc')
            ->limit(10)
            ->get()
            ->map(function($thread) {
                return [
                    'id' => $thread->id,
                    'subject' => $thread->subject ?: 'No Subject',
                    'board' => $thread->board->code,
                    'reply_count' => $thread->reply_count,
                    'updated_at' => $thread->bumped_at,
                ];
            });

        return response()->json(['threads' => $threads]);
    }

    /**
     * Get random thread
     */
    public function getRandomThread()
    {
        $thread = Thread::with('board')
            ->inRandomOrder()
            ->first();

        if (!$thread) {
            return response()->json(['error' => 'No threads found'], 404);
        }

        return response()->json([
            'url' => "/boards/{$thread->board->name}/thread/{$thread->id}",
            'thread' => [
                'id' => $thread->id,
                'subject' => $thread->subject,
                'board' => $thread->board->name,
            ]
        ]);
    }

    /**
     * Get previous thread in board
     */
    public function getPreviousThread($threadId)
    {
        $currentThread = Thread::with('board')->find($threadId);
        
        if (!$currentThread) {
            return response()->json(['error' => 'Thread not found'], 404);
        }

        $previousThread = Thread::with('board')
            ->where('board_id', $currentThread->board_id)
            ->where('id', '<', $threadId)
            ->orderBy('id', 'desc')
            ->first();

        if (!$previousThread) {
            // Wrap around to the last thread in the board
            $previousThread = Thread::with('board')
                ->where('board_id', $currentThread->board_id)
                ->orderBy('id', 'desc')
                ->first();
        }

        if (!$previousThread) {
            return response()->json(['error' => 'No previous thread found'], 404);
        }

        return response()->json([
            'url' => "/boards/{$previousThread->board->name}/thread/{$previousThread->id}",
            'thread' => [
                'id' => $previousThread->id,
                'subject' => $previousThread->subject,
                'board' => $previousThread->board->name,
            ]
        ]);
    }

    /**
     * Get next thread in board
     */
    public function getNextThread($threadId)
    {
        $currentThread = Thread::with('board')->find($threadId);
        
        if (!$currentThread) {
            return response()->json(['error' => 'Thread not found'], 404);
        }

        $nextThread = Thread::with('board')
            ->where('board_id', $currentThread->board_id)
            ->where('id', '>', $threadId)
            ->orderBy('id', 'asc')
            ->first();

        if (!$nextThread) {
            // Wrap around to the first thread in the board
            $nextThread = Thread::with('board')
                ->where('board_id', $currentThread->board_id)
                ->orderBy('id', 'asc')
                ->first();
        }

        if (!$nextThread) {
            return response()->json(['error' => 'No next thread found'], 404);
        }

        return response()->json([
            'url' => "/boards/{$nextThread->board->name}/thread/{$nextThread->id}",
            'thread' => [
                'id' => $nextThread->id,
                'subject' => $nextThread->subject,
                'board' => $nextThread->board->name,
            ]
        ]);
    }

    /**
     * Calculate search relevance score
     */
    private function calculateRelevance($result, $query)
    {
        $score = 0;
        $query = strtolower($query);

        if ($result['type'] === 'thread') {
            $subject = strtolower($result['subject'] ?? '');
            
            // Exact match in subject
            if (strpos($subject, $query) !== false) {
                $score += 100;
            }
            
            // Word match in subject
            foreach (explode(' ', $query) as $word) {
                if (strpos($subject, $word) !== false) {
                    $score += 50;
                }
            }
            
            // Recent activity bonus
            $hoursAgo = now()->diffInHours($result['updated_at']);
            if ($hoursAgo < 24) {
                $score += 20;
            } elseif ($hoursAgo < 168) { // 1 week
                $score += 10;
            }
            
            // Popular thread bonus
            if ($result['reply_count'] > 10) {
                $score += 5;
            }
            
        } elseif ($result['type'] === 'board') {
            $name = strtolower($result['name']);
            $code = strtolower($result['code']);
            
            // Exact match in code (highest priority)
            if ($code === $query) {
                $score += 200;
            }
            
            // Partial match in code
            if (strpos($code, $query) !== false) {
                $score += 150;
            }
            
            // Match in name
            if (strpos($name, $query) !== false) {
                $score += 75;
            }
        }

        return $score;
    }
}