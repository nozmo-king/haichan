<?php

namespace App\Http\Controllers;

use App\Models\Board;
use App\Models\Challenge;
use App\Services\ChallengeVerifier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class MiningChallengeController extends Controller
{
    protected $verifier;

    public function __construct(ChallengeVerifier $verifier)
    {
        $this->verifier = $verifier;
    }

    public function issue(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'board_code' => 'nullable|string|exists:boards,name',
            'target_type' => 'required|string|in:thread,reply,post,general',
            'target_id' => 'nullable|string',
            'action' => 'required|string|in:bump,create,mine',
            'difficulty' => 'required|string|in:21,21e,21e8,21e80,21e800,21e8000,000021e8,000,111,222,333,444,555,666,777,888,999,aaa,bbb,ccc,ddd,eee,fff,ace,bad,cab,dad,ded,fab,fed,beef,cafe,face,babe,fade,dead,deed,feed,c0de,b00b,1337,pwnd,rekt,epic,Chad,deadbeef',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid challenge request',
                'errors' => $validator->errors(),
            ], 422);
        }

        $minimumDifficulty = $this->verifier->getMinimumDifficulty($request->action, $request->target_type);
        $requestedDifficultyValue = $this->verifier->getDifficultyValue($request->difficulty);

        if ($requestedDifficultyValue < $minimumDifficulty['value']) {
            return response()->json([
                'success' => false,
                'message' => 'Difficulty too low for this action',
                'errors' => [
                    'difficulty' => [
                        "Minimum difficulty for {$request->action} action is '{$minimumDifficulty['pattern']}' (value: {$minimumDifficulty['value']}). Requested difficulty '{$request->difficulty}' (value: {$requestedDifficultyValue}) is too low."
                    ]
                ],
                'minimum_required' => $minimumDifficulty['pattern'],
            ], 422);
        }

        $boardId = null;
        if ($request->has('board_code')) {
            $board = Board::where('name', $request->board_code)->first();
            if ($board) {
                $boardId = $board->id;
            }
        }

        $validatedTargetId = null;
        if ($request->has('target_id') && $request->target_id) {
            if ($request->target_type === 'thread') {
                $thread = \App\Models\Thread::find($request->target_id);
                if (!$thread) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Invalid target thread',
                        'errors' => ['target_id' => ['Thread not found']],
                    ], 422);
                }
                $validatedTargetId = $thread->id;
            } elseif ($request->target_type === 'post' || $request->target_type === 'reply') {
                $post = \App\Models\Post::find($request->target_id);
                if (!$post) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Invalid target post',
                        'errors' => ['target_id' => ['Post not found']],
                    ], 422);
                }
                $validatedTargetId = $post->id;
            }
        }

        $serverNonce = bin2hex(random_bytes(32));

        $canonicalPayload = [
            'board_id' => $boardId,
            'target_type' => $request->target_type,
            'target_id' => $validatedTargetId,
            'action' => $request->action,
            'difficulty' => $request->difficulty,
            'server_nonce' => $serverNonce,
            'issued_at' => now()->timestamp,
        ];

        $hmacSignature = $this->verifier->computeHmacSignature($canonicalPayload);

        $token = (string) Str::uuid();
        $issuedAt = now();
        $expiresAt = now()->addMinutes(5);

        $userId = session('bitcoin_auth_id');

        $challenge = Challenge::create([
            'token' => $token,
            'board_id' => $boardId,
            'target_type' => $request->target_type,
            'target_id' => $validatedTargetId,
            'action' => $request->action,
            'difficulty' => $request->difficulty,
            'server_nonce' => $serverNonce,
            'canonical_payload' => $canonicalPayload,
            'hmac_signature' => $hmacSignature,
            'issued_at' => $issuedAt,
            'expires_at' => $expiresAt,
            'user_id' => $userId,
            'ip_address' => $request->ip(),
        ]);

        Log::info('Challenge issued', [
            'token' => $token,
            'target_type' => $request->target_type,
            'target_id' => $validatedTargetId,
            'difficulty' => $request->difficulty,
            'difficulty_value' => $requestedDifficultyValue,
            'minimum_required' => $minimumDifficulty['value'],
            'expires_at' => $expiresAt,
        ]);

        return response()->json([
            'success' => true,
            'token' => $token,
            'canonical_payload' => $canonicalPayload,
            'signature' => $hmacSignature,
            'expires_in' => 300,
            'expires_at' => $expiresAt->toIso8601String(),
        ]);
    }
}
