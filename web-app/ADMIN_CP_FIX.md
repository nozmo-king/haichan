# Admin CP 404 Fix

## Issue

Admin Control Panel at `/admin` was returning 404 error.

## Root Cause

The `admin` middleware was referenced in routes but not registered in the middleware alias list in `bootstrap/app.php`.

## Fix

Added the missing middleware alias:

```php
// bootstrap/app.php
->withMiddleware(function (Middleware $middleware): void {
    $middleware->alias([
        'require.auth' => \App\Http\Middleware\RequireAuth::class,
        'validate.friend.code' => \App\Http\Middleware\ValidateFriendCode::class,
        'bitcoin.auth' => \App\Http\Middleware\BitcoinAuth::class,
        'security.headers' => \App\Http\Middleware\SecurityHeaders::class,
        'admin' => \App\Http\Middleware\AdminRequired::class,  // <-- ADDED
    ]);
    
    // Apply security headers globally
    $middleware->append(\App\Http\Middleware\SecurityHeaders::class);
})
```

## How Admin Middleware Works

The `AdminRequired` middleware (`app/Http/Middleware/AdminRequired.php`):

1. Checks if user is logged in via Bitcoin auth
2. Verifies user has `is_admin` flag set to true
3. Refreshes user data to ensure current privileges
4. Returns 403 if any check fails

## Admin Routes Protected

All routes under `/admin` prefix are now properly protected:

- `GET /admin` - Admin dashboard
- `GET /admin/users` - User management
- `GET /admin/forum` - Forum management
- `GET /admin/keys` - Public key management
- And all other admin endpoints

## Access Requirements

To access Admin CP:
1. Must be logged in with Bitcoin authentication
2. User account must have `is_admin = true` in database
3. Session must be valid

## Files Modified

- `/root/haichan/web-app/bootstrap/app.php` - Added admin middleware alias

## Status

✅ **FIXED** - Admin CP is now accessible at `/admin` for authorized users
