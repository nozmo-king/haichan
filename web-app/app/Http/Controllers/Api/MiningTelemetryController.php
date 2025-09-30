<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;

class MiningTelemetryController extends Controller
{
    public function ingest(Request $request)
    {
        $key = 'telemetry:'.sha1(($request->ip() ?? 'x').($request->input('session_id') ?? 'x'));
        if (RateLimiter::tooManyAttempts($key, 30)) { // 30 per 5 minutes cap
            return response()->json(['ok' => true]);
        }
        RateLimiter::hit($key, 300);

        $data = $request->validate([
            'session_id' => 'required|string|max:64',
            'ts' => 'required|integer',
            'kind' => 'nullable|string|max:20',
            'pattern' => 'nullable|string|max:16',
            'attempts' => 'nullable|integer|min:0',
            'hash' => 'nullable|string|size:64',
        ]);

        Log::channel('daily')->info('mining.telemetry', [
            'ip' => $request->ip(),
            'user_id' => session('bitcoin_auth_id'),
            'data' => $data,
        ]);

        return response()->json(['ok' => true]);
    }
}

