<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Services\FriendCodeService;

class ValidateFriendCode
{
    public function __construct(
        private FriendCodeService $friendCodeService
    ) {}

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $friendCode = $request->input('friend_code') ?? $request->route('friendCode');

        if (!$friendCode) {
            return $this->redirectWithError('Friend code is required for registration.');
        }

        $validCode = $this->friendCodeService->validateFriendCode($friendCode);

        if (!$validCode) {
            return $this->redirectWithError('Invalid or expired friend code.');
        }

        $request->merge(['validated_friend_code' => $validCode]);

        return $next($request);
    }

    private function redirectWithError(string $message): Response
    {
        if (request()->expectsJson()) {
            return response()->json(['error' => $message], 400);
        }

        return redirect()->back()
            ->withErrors(['friend_code' => $message])
            ->withInput();
    }
}
