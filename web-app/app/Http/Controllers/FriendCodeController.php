<?php

namespace App\Http\Controllers;

use App\Services\FriendCodeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FriendCodeController extends Controller
{
    public function __construct(
        private FriendCodeService $friendCodeService
    ) {}

    public function index(Request $request)
    {
        $user = $request->user();
        $friendCodes = $user->friendCodes()->latest()->paginate(10);
        $stats = $this->friendCodeService->getFriendCodeStats($user);

        return view('friend-codes.index', compact('friendCodes', 'stats'));
    }

    public function generate(Request $request)
    {
        $user = $request->user();

        $friendCode = $this->friendCodeService->generateFriendCode($user);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'code' => $friendCode->code,
                'expires_at' => $friendCode->expires_at?->toISOString(),
            ]);
        }

        return redirect()->back()->with('success', 'Friend code generated successfully!');
    }

    public function validate(Request $request): JsonResponse
    {
        $request->validate([
            'code' => 'required|string',
        ]);

        $friendCode = $this->friendCodeService->validateFriendCode($request->code);

        return response()->json([
            'valid' => $friendCode !== null,
            'message' => $friendCode ? 'Valid friend code' : 'Invalid or expired friend code',
        ]);
    }

    public function stats(Request $request): JsonResponse
    {
        $user = $request->user();
        $stats = $this->friendCodeService->getFriendCodeStats($user);

        return response()->json($stats);
    }
}
