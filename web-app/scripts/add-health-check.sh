#!/bin/bash
# Health Check Endpoint Setup
# Run this to add health monitoring

cd /root/haichan/web-app

# Create health check route
cat >> routes/api.php << 'EOFROUTE'

// Health check endpoint
Route::get('/health', function () {
    try {
        // Check database
        $dbStatus = 'connected';
        try {
            DB::connection()->getPdo();
        } catch (\Exception $e) {
            $dbStatus = 'disconnected';
        }
        
        // Check cache
        $cacheStatus = 'working';
        try {
            Cache::put('health-check', true, 5);
            if (!Cache::has('health-check')) {
                $cacheStatus = 'failed';
            }
        } catch (\Exception $e) {
            $cacheStatus = 'failed';
        }
        
        // Check storage writable
        $storageStatus = is_writable(storage_path('logs')) ? 'writable' : 'readonly';
        
        return response()->json([
            'status' => 'ok',
            'timestamp' => now()->toIso8601String(),
            'services' => [
                'database' => $dbStatus,
                'cache' => $cacheStatus,
                'storage' => $storageStatus,
            ],
            'app' => [
                'name' => config('app.name'),
                'env' => config('app.env'),
                'version' => '1.0.0',
            ],
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => $e->getMessage(),
        ], 500);
    }
});
EOFROUTE

echo "✓ Health check route added to routes/api.php"
echo ""
echo "Test with: curl http://127.0.0.1:8080/api/health"
