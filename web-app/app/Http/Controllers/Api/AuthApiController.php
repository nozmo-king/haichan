<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use App\Models\User;
use App\Models\AllowedPublicKey;

class AuthApiController extends Controller
{
    public function getChallenge(Request $request)
    {
        $request->validate([
            'public_key' => 'required|string'
        ]);

        $user = User::findOrCreateForPublicKey($request->public_key);
        
        if (!$user) {
            return response()->json(['error' => 'Public key not authorized'], 403);
        }

        $challenge = $user->generateChallenge();

        return response()->json([
            'challenge' => $challenge,
            'user_id' => $user->id
        ]);
    }

    public function login(Request $request)
    {
        $request->validate([
            'signature' => 'required|string',
            'challenge' => 'required|string',
            'user_id' => 'required|integer'
        ]);

        $user = User::find($request->user_id);
        
        if (!$user) {
            return response()->json(['error' => 'User not found'], 404);
        }

        // Verify signature using your existing verification method
        if (!$user->verifySignature($request->signature, $request->challenge)) {
            return response()->json(['error' => 'Invalid signature'], 401);
        }

        // Clear the challenge to prevent replay attacks
        $user->clearChallenge();

        $token = $user->createToken('api-token')->plainTextToken;

        return response()->json([
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'public_key' => $user->allowedPublicKey->public_key
            ]
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        
        return response()->json(['message' => 'Logged out successfully']);
    }
}