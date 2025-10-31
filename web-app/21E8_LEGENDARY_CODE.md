# 21E8 Legendary Invite Code

## Code Details

**Code**: `21E80000000000`

### Configuration
- **Type**: Legendary Genesis Code
- **Uses Remaining**: 21
- **Mining Bonus**: 1.0x (normal, not boosted)
- **Special Reward**: **210,000 Points Preloaded** on registration

## What This Means

Instead of getting a mining bonus (which would be absurdly overpowered), users who register with this code get:

1. **210,000 points** added to their account immediately upon registration
2. **Level 210** instantly (for prestige and status)
3. **Mining power 2.1x** (fixed, not level-based)
4. Balanced mining from then on

## Why This Is Better

- **Not exploitable** - They get the points once, not forever
- **Fair reward** - 210,000 points is substantial but not game-breaking
- **Level appropriate** - High level gives them status and respect
- **Balanced** - They still mine at a reasonable rate going forward

## How It Works

When a user registers with code `21E80000000000`:

1. Registration proceeds normally
2. After invite code is used, special handler checks if code is '21E80000000000'
3. If yes:
   - `total_pow_points` set to 210,000
   - `level` calculated: 210
   - `mining_power` adjusted based on level: 22.0x
4. User logs in with 210k points ready to spend

## Technical Implementation

Location: `app/Http/Controllers/AuthController.php`

```php
// Special handling for 21E8 legendary code - preload points instead of bonus
if ($inviteCode->code === '21E80000000000') {
    $user->total_pow_points = 210000;
    $user->level = 210; // Set level to 210 but don't let it affect mining power
    $user->mining_power = 2.1; // Fixed 2.1x mining power, not level-based
    $user->save();
    
    \Log::info('21E8 legendary code used', [
        'user_id' => $user->id,
        'username' => $user->username,
        'preloaded_points' => 210000,
        'level' => 210,
        'mining_power' => 2.1
    ]);
}
```

## Usage Tracking

- Each use is logged
- Only 21 total uses available
- Once depleted, code becomes inactive
- Can track who got the legendary start in logs

## Comparison with Other Codes

| Code Type | Mining Bonus | Special Reward | Uses |
|-----------|-------------|----------------|------|
| Regular (HAICHAN01-05) | 1.0x | None | 10 each |
| Premium (PREMIUM01-02) | 1.5x | None | 5 each |
| Genesis (GENESIS2025) | 2.0x | None | 100 |
| **Legendary (21E80000000000)** | **2.1x** | **210,000 points + Level 210** | **21** |

## Status

✅ **ACTIVE** - Code is ready to use and will preload 210,000 points on registration
