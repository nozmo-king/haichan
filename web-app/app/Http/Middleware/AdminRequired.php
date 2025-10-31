<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class AdminRequired
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // Check if user is logged in with Bitcoin auth
        $userId = session('bitcoin_auth_id');
        $user = session('bitcoin_auth_user');
        
        if (!$userId || !$user) {
            abort(403, 'Authentication required');
        }
        
        // Check if user is admin
        if (!$user->is_admin) {
            abort(403, 'Admin access required');
        }
        
        // Refresh user data to ensure current privileges
        $freshUser = \App\Models\BitcoinAuth::find($userId);
        if (!$freshUser || !$freshUser->is_admin) {
            // Clear stale session data
            session()->forget(['bitcoin_auth_id', 'bitcoin_auth_user']);
            abort(403, 'Admin privileges revoked');
        }
        
        return $next($request);
    }
}