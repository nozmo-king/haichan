# Invite Codes Created

Generated on: 2025-10-31

## Active Invite Codes

### Regular Codes (1.0x Mining Bonus)
- **HAICHAN01** - 10 uses remaining
- **HAICHAN02** - 10 uses remaining
- **HAICHAN03** - 10 uses remaining
- **HAICHAN04** - 10 uses remaining
- **HAICHAN05** - 10 uses remaining

### Premium Codes (1.5x Mining Bonus)
- **PREMIUM01** - 5 uses remaining
- **PREMIUM02** - 5 uses remaining

### Genesis Code (2.0x Mining Bonus)
- **GENESIS2025** - 100 uses remaining

### 🔥 LEGENDARY CODE (210,000 Points + 2.1x Mining)
- **21E80000000000** - 21 uses remaining (LEGENDARY START!)

## Summary

- **Total Active Codes**: 9
- **Total Available Uses**: 181
- **Regular Codes**: 5 (50 uses)
- **Premium Codes**: 2 (10 uses)
- **Genesis Codes**: 1 (100 uses)
- **Legendary Codes**: 1 (21 uses)

## Mining Bonuses

- **1.0x** - Standard mining power
- **1.5x** - 50% bonus mining power (Premium codes)
- **2.0x** - Double mining power (Genesis codes)

## Special Rewards

- **21E80000000000** - Preloads 210,000 points on registration (Level 210 instantly!) + 2.1x mining power

## Usage

Users can register using these codes at the registration page. Each code has a limited number of uses and may grant bonus mining power.

## Admin Management

View and manage invite codes in the Admin CP at `/admin` → Invite Codes section.

### Creating More Codes

To create additional invite codes, you can:

1. Use the Admin CP interface
2. Run a similar PHP script
3. Use tinker: `php artisan tinker`

Example tinker command:
```php
\App\Models\InviteCode::create([
    'code' => 'YOURCODE',
    'created_by' => 1,
    'uses_remaining' => 10,
    'expires_at' => null,
    'is_genesis' => false,
    'mining_bonus' => 1.0,
]);
```

## Notes

- Codes are case-sensitive
- Genesis codes grant special privileges
- Mining bonus applies to all future mining activities
- Expired or depleted codes cannot be used
