# Form Mining Fix
**Date:** 2025-10-28 04:20 UTC

## Issue
ReplyFormMiner couldn't find forms on board pages - was only looking for `.unified-post-form` class, but thread creation forms use `#new-thread-form` ID.

## Error Logs
```
❌ No reply form found with class .unified-post-form
🔍 Available forms on page: 2
🔍 Available form classes: (2) ['', '']
```

## Root Cause
The form finder was only checking for reply forms (`.unified-post-form`) and not checking for thread creation forms (`#new-thread-form`).

## Solution
Updated form finder to check both:
```javascript
// Before
const replyForm = document.querySelector('.unified-post-form');

// After  
const replyForm = document.querySelector('.unified-post-form') || 
                 document.querySelector('#new-thread-form');
```

## Forms on Site

### Reply Form (in threads)
- **Selector:** `.unified-post-form`
- **Location:** `/board/thread_id`
- **File:** `resources/views/boards/thread.blade.php`

### Thread Creation Form (on board)
- **Selector:** `#new-thread-form`
- **Location:** `/board`
- **File:** `resources/views/boards/show.blade.php`

## Testing

After clearing cache, visit:
1. **Board page:** `http://127.0.0.1:8080/gen`
   - Should see: `🔍 Form type: new-thread-form`
   
2. **Thread page:** `http://127.0.0.1:8080/gen/1`
   - Should see: `🔍 Form type: unified-post-form`

## Files Modified
- `public/js/simple-pow.js`
  - Updated `ReplyFormMiner.setup()` to find both form types

---
*Fixed: 2025-10-28 04:20 UTC*
