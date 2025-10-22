# Proof of Work (PoW) System Audit Report
**Date:** October 20, 2025  
**Auditor:** Automated Code Analysis  
**Scope:** Haichan PoW Implementation

---

## Executive Summary

This audit identifies critical security vulnerabilities and issues in the Haichan Proof of Work system that could allow malicious users to exploit the mining mechanism.

---

## Critical Issues

### 1. **DUMMY HASH BYPASS VULNERABILITY** 🔴 CRITICAL
**Location:** `ProofOfWorkController.php` lines 82-91, 547-553  
**Issue:** Hardcoded dummy hash check can be bypassed with variations  

**Current Code:**
```php
if ($hash === '21e8000000000000000000000000000000000000000000000000000000000000') {
    // Reject
}
```

**Problem:** Only checks for exact match. Attackers can use:
- `21e8000000000000000000000000000000000000000000000000000000000001` (add 1 at end)
- `21e80000000000000000000000000000000000000000000000000000000000000` (extra zero)
- Any other variation

**Recommendation:** Implement pattern-based detection:
```php
// Check if hash is suspiciously regular (too many zeros)
$zeroCount = substr_count($hash, '0');
if ($zeroCount > 50) { // 64 chars, >78% zeros is suspicious
    return response()->json(['success' => false, 'message' => 'Invalid hash pattern'], 400);
}
```

---

### 2. **RACE CONDITION IN DUPLICATE CHECKING** 🔴 CRITICAL
**Location:** `ProofOfWorkController.php` lines 73-78, 253-255  
**Issue:** Duplicate check happens before database transaction

**Current Flow:**
1. Check if hash exists (line 73)
2. Create proof record (line 112)
3. **Race window:** Multiple requests can pass check #1 simultaneously

**Problem:** Under load, same hash can be submitted multiple times and all accepted if they arrive simultaneously.

**Recommendation:** Use database unique constraint + transaction handling:
```php
// Add to migration:
$table->unique('hash');

// In controller:
try {
    $proof = ProofOfWork::create([...]);
} catch (\Illuminate\Database\QueryException $e) {
    if ($e->getCode() == 23000) { // Duplicate key
        return response()->json(['success' => false, 'message' => 'Duplicate proof'], 400);
    }
    throw $e;
}
```

---

### 3. **NO NONCE VERIFICATION** 🔴 CRITICAL
**Location:** `ProofOfWorkController.php` line 116  
**Issue:** Nonce is stored as 0 instead of actual client nonce

**Current Code:**
```php
'nonce' => 0,  // Line 116 - HARDCODED TO ZERO!
```

**Problem:** 
- Server doesn't verify the actual nonce used
- Client can submit any hash without proving work
- Makes PoW system effectively bypassable

**Recommendation:**
```php
// Store and verify actual nonce
'nonce' => $request->input('client_nonce'),

// Add verification in ChallengeVerifier:
$reconstructedData = $challenge->canonical_payload . $clientNonce;
$serverHash = hash('sha256', $reconstructedData);
if ($serverHash !== $submittedHash) {
    return ['valid' => false, 'error' => 'Hash verification failed'];
}
```

---

### 4. **INSUFFICIENT HASH PATTERN VALIDATION** 🟡 HIGH
**Location:** `ProofOfWorkController.php` lines 242-250  
**Issue:** Only checks if hash starts with pattern, not if work was actually done

**Current Code:**
```php
if (!str_starts_with(strtolower($submittedHash), strtolower($pattern))) {
    return ['valid' => false, 'error' => 'Hash does not match pattern.'];
}
```

**Problem:**
- Attacker can pre-compute hashes starting with '21e8'
- No verification that the hash was computed from the challenge data
- Pattern check without data verification is meaningless

**Recommendation:**
```php
// Verify hash was computed from challenge + nonce
$expectedData = json_encode($challenge->canonical_payload) . $nonce;
$computedHash = hash('sha256', $expectedData);

if ($computedHash !== $submittedHash) {
    Log::error('Hash tampering detected', [
        'expected' => $computedHash,
        'submitted' => $submittedHash
    ]);
    return ['valid' => false, 'error' => 'Invalid proof'];
}
```

---

### 5. **MOUSEOVER MINING AUTHENTICATION BYPASS** 🟡 HIGH
**Location:** `ProofOfWorkController.php` lines 556-562  
**Issue:** Authentication check happens AFTER validation

**Current Code:**
```php
// Validation happens first (lines 529-543)
// THEN authentication check (lines 556-562)
```

**Problem:**
- Wastes server resources on unauthenticated requests
- Could be used for DoS attacks

**Recommendation:**
```php
// Move authentication to top of function
$userId = session('bitcoin_auth_id');
if (!$userId) {
    return response()->json([
        'success' => false,
        'message' => 'Authentication required'
    ], 401);
}
// ... then validate
```

---

### 6. **POINT CALCULATION MANIPULATION** 🟡 HIGH
**Location:** `ProofOfWorkController.php` lines 287-351, `MiningController.php` lines 192-244  
**Issue:** Inconsistent point calculation between controllers

**Problems:**
- Two different point calculation methods exist
- Bonus multipliers can stack unexpectedly
- Special pattern bonuses in `MiningController` are exploitable

**Example Exploit in MiningController.php:**
```php
// Lines 222-228: Bonus stacking
if (str_starts_with($hash, '21e800') && $expectedPattern !== '21e800') {
    $basePoints *= 25;  // 25x multiplier
}
// Then line 232-241: More bonuses can apply!
if (str_starts_with($hash, '000')) {
    $basePoints *= 10;  // Another 10x!
}
```

**Result:** Hash starting with `000` and `21e800` could get 250x points!

**Recommendation:**
- Consolidate to one point calculation method
- Use fixed point values, not multipliers
- Document all special patterns

---

### 7. **MISSING RATE LIMITING** 🟡 HIGH
**Location:** All submission endpoints  
**Issue:** No rate limiting on proof submissions

**Problem:**
- Attacker can spam submissions
- Could overwhelm database
- Could claim massive points before detection

**Recommendation:**
```php
// Add to ProofOfWorkController
use Illuminate\Support\Facades\RateLimiter;

public function submitProof(Request $request) {
    $key = 'pow-submit:' . $request->ip();
    
    if (RateLimiter::tooManyAttempts($key, 10)) { // 10 per minute
        return response()->json([
            'success' => false,
            'message' => 'Too many submissions'
        ], 429);
    }
    
    RateLimiter::hit($key, 60);
    // ... rest of code
}
```

---

### 8. **CHALLENGE REUSE VULNERABILITY** 🟡 HIGH
**Location:** `ProofOfWorkController.php` line 102  
**Issue:** Challenge marked as used AFTER points awarded

**Current Code:**
```php
// Line 93: Calculate points
// Lines 103-141: Save proof and award points
// Line 102: Mark challenge as used
```

**Problem:**
- If error occurs after point award but before marking used
- Challenge remains valid and can be reused
- Transaction doesn't cover challenge update

**Recommendation:**
```php
DB::transaction(function () use ($challenge, ...) {
    // Mark challenge as used FIRST
    $challenge->markAsUsed();
    
    // Then create proof
    $proof = ProofOfWork::create([...]);
    
    // Then award points
    if ($threadId) {
        $thread->increment('bump_score', $points);
    }
});
```

---

### 9. **WEAK ENTROPY IN CHALLENGE GENERATION** 🟠 MEDIUM
**Location:** Need to examine Challenge model  
**Issue:** Unknown - requires audit of Challenge::create()

**Recommendation:**
- Ensure challenges use cryptographically secure random
- Include timestamp, user_id, and server secret in challenge
- Prevent challenge prediction attacks

---

### 10. **NO MONITORING OR ANOMALY DETECTION** 🟠 MEDIUM
**Issue:** System has logging but no automated detection

**Missing:**
- Alerts for suspicious patterns (e.g., 100 proofs from one IP in 1 minute)
- Monitoring for impossible hash rates
- Detection of pre-computed rainbow tables

**Recommendation:**
```php
// Add anomaly detection service
class PoWAnomalyDetector {
    public function checkSubmission($userId, $hash, $nonce, $timeTaken) {
        // Check if hash rate is impossible
        if ($timeTaken < 0.001) { // Less than 1ms? Suspicious!
            Log::warning('Impossible hash rate detected', [
                'user_id' => $userId,
                'time' => $timeTaken
            ]);
            return false;
        }
        
        // Check submission frequency
        $recentCount = ProofOfWork::where('user_id', $userId)
            ->where('created_at', '>', now()->subMinute())
            ->count();
            
        if ($recentCount > 20) { // More than 20/min suspicious
            Log::warning('High submission rate', ['user_id' => $userId]);
            return false;
        }
        
        return true;
    }
}
```

---

## Additional Concerns

### Code Quality Issues

1. **Dead Code:** `.cjs` files in root appear to be test scripts and should be removed from production
2. **Duplicate Logic:** Two controllers (`ProofOfWorkController`, `MiningController`) with overlapping functionality
3. **Missing Documentation:** Point values and patterns not documented
4. **Inconsistent Naming:** `client_nonce` vs `nonce`, `difficulty` vs `pattern`

### Files Requiring Review

- `/root/haichan/web-app/sign-challenge.cjs` - Test file, remove from production
- `/root/haichan/web-app/working-sign.cjs` - Test file, remove from production
- `/root/haichan/web-app/simple-sign.cjs` - Test file, remove from production
- `/root/haichan/web-app/ios-mimic.cjs` - Test file, remove from production

---

## Recommended Immediate Actions

1. **Deploy unique constraint on ProofOfWork.hash** - Prevents duplicate submissions
2. **Fix nonce verification** - Actually verify client work
3. **Add rate limiting** - Prevent spam attacks
4. **Consolidate point calculation** - One source of truth
5. **Move authentication checks earlier** - Reduce attack surface
6. **Remove dummy hash check** - Replace with entropy analysis
7. **Implement transaction wrapping** - Prevent race conditions
8. **Add monitoring** - Detect exploitation attempts

---

## Long-term Recommendations

1. Create comprehensive test suite for PoW system
2. Implement challenge difficulty adjustment based on network hash rate
3. Add user reputation system to flag suspicious mining patterns
4. Create admin dashboard for PoW monitoring
5. Document all special hash patterns and their point values
6. Consider implementing CAPTCHA for high-value submissions
7. Add client-side proof verification before submission
8. Implement proof batching to reduce server load

---

## Conclusion

The current PoW system has **multiple critical vulnerabilities** that allow exploitation. The most severe issues are:

1. No actual nonce verification (work can be faked)
2. Race conditions in duplicate checking
3. Insufficient hash validation
4. Point calculation manipulation potential

**Estimated Risk Level: CRITICAL**  
**Recommended Action: Immediate remediation required**

---

*End of Audit Report*
