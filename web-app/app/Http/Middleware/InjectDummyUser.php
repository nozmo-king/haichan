<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\BitcoinAuth;

class InjectDummyUser
{
    public function handle(Request $request, Closure $next)
    {
        $dummyUser = BitcoinAuth::where('address', 'DUMMY_TESTER_ADDRESS')->first();

        if (!$dummyUser) {
            $dummyUser = BitcoinAuth::create([
                'public_key' => 'dummy_public_key_for_testing',
                'address' => 'DUMMY_TESTER_ADDRESS',
                'username' => 'DummyTester',
                'display_name' => 'Dummy Tester',
                'bio' => 'Auto-generated test user for /d/ board',
                'mining_power' => 1.0,
                'total_pow_points' => 0,
                'invite_code' => 'DUMMY000000',
                'mining_streak' => 0,
                'level' => 1,
                'is_banned' => false,
                'is_admin' => false,
                'admin_level' => 0,
            ]);
        }

        Auth::onceUsingId($dummyUser->id);

        return $next($request);
    }
}
