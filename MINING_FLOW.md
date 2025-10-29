# Haichan Mining System - Complete Flow

## Overview
Haichan uses a challenge-response proof-of-work system where users must find SHA-256 hashes matching specific patterns to post content.

## Frontend Implementation

### Main Mining Code
- **Location**: `/web-app/public/js/simple-pow.js`
- **Entry Point**: `window.simplePoW = new SimpleProofOfWork()`
- **Reply Handler**: `window.replyFormMiner = new ReplyFormMiner(window.simplePoW)`

### Mining Flow (Client Side)

1. **User Types Content**
   - Reply form detects input via event listener
   - After 1.5 seconds of typing, mining auto-starts

2. **Acquire Challenge**
   ```javascript
   // Request from: POST /api/pow/challenge
   const proof = await simplePoW.acquireProofFor({
       board_code: 'gen',
       target_type: 'reply',
       target_id: threadId,
       action: 'create',
       difficulty: '21e8',
       post_draft: { body: content, attachments: [], refs: [] },
       user_pubkey_hex: localStorage.getItem('user_pubkey')
   });
   ```

3. **Server Returns Challenge**
   ```json
   {
       "challenge_id": "uuid",
       "nonce_start": 0,
       "nonce_end": 1000000,
       "difficulty": "21e8",
       "canonical_payload": {...}
   }
   ```

4. **Client Mines Hash**
   - Iterate through nonces
   - For each nonce: `hash = SHA256(canonical_payload + nonce)`
   - Check if hash starts with difficulty pattern (e.g., "21e8")
   - Continue until matching hash found

5. **Submit Proof**
   - Fill hidden form fields:
     - `pow_nonce`: winning nonce
     - `pow_hash`: resulting hash
     - `pow_challenge_id`: challenge UUID

6. **Form Submits to Backend**
   - Route: `POST /{board}/{thread}/reply`
   - Controller: `ForumController@storeReply`

## Backend Validation

### Verification Flow
1. **Controller**: `ForumController@storeReplyWithNewPoW()`
2. **Verifier**: `ChallengeVerifier@verifyChallenge()`
   - Loads challenge from database
   - Checks not expired (30 second window)
   - Checks not already used
   - Recomputes: `SHA256(canonical_payload + nonce)`
   - Verifies hash matches submitted hash
   - Verifies hash starts with required pattern

3. **On Success**
   - Create Post record
   - Create ProofOfWork record
   - Award points to user
   - Mark challenge as used

### Point Calculation
```php
private function calculatePoWPoints($hash, $expectedPattern)
{
    $pointMap = [
        '21'    => 0.1,
        '21e'   => 0.5,
        '21e8'  => 100,
        '21e80' => 500,
        '21e800'=> 2500,
    ];
    
    // Bonus for rare patterns:
    // - Starts with '000': 10x
    // - Starts with '666': 15x
    // - Contains 'dead': 8x
}
```

## Files Overview

### Working Files
- `/web-app/public/js/simple-pow.js` - Client mining logic
- `/web-app/app/Http/Controllers/ForumController.php` - Post creation
- `/web-app/app/Services/ChallengeVerifier.php` - Challenge verification
- `/web-app/resources/views/boards/thread.blade.php` - Reply form

### Dead/Broken References (REMOVED)
- ❌ `/web-app/resources/js/mining-brain.js` - DOESN'T EXIST
- ❌ `/web-app/resources/js/global-mining.js` - DOESN'T EXIST
- ✅ Removed import from `app.js`

## Rate Limiting

### Reply Rate Limits
- Authenticated users: 10 replies/minute
- Per IP: 15 replies/minute

### Thread Rate Limits  
- Authenticated users: 3 threads/5 minutes
- Per IP: 5 threads/5 minutes

## Security Features

1. **Challenge Expiry**: 30 seconds
2. **One-Time Use**: Challenges marked as used after successful post
3. **Server-Side Verification**: All hashes recomputed and verified
4. **Pattern Detection**: Suspicious hashes (too many zeros) rejected
5. **Rate Limiting**: Prevents spam

## Difficulty Levels

- `21` - Easy (0.1 points) - ~2^8 attempts
- `21e` - Medium (0.5 points) - ~2^12 attempts  
- `21e8` - Hard (100 points) - ~2^16 attempts
- `21e80` - Very Hard (500 points) - ~2^20 attempts
- `21e800` - Extreme (2500 points) - ~2^24 attempts

## Recent Fix (2025-10-27)

Fixed reply form mining initialization:
- Made `miningStatus` element optional (graceful degradation)
- Added `classList.remove('tui-btn-disabled')` on mining completion
- Added null checks throughout mining flow
- Form now properly enables submit button after mining
