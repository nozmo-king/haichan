# Haichan PoW System - cURL Examples

## Prerequisites
```bash
export BASE_URL="http://localhost:8000"
export TOKEN="your_sanctum_token_here"
```

## 1. Get PoW Parameters
```bash
curl -X GET "${BASE_URL}/api/pow.params" \
  -H "Accept: application/json" | jq
```

**Expected Response:**
```json
{
  "mode": "vanity_prefix",
  "default_prefix": "21e8",
  "min_miner_version": 1,
  "suggested_prefix_by_load": "21e8"
}
```

## 2. Begin Thread Creation

```bash
OP_ID=$(uuidgen)

curl -X POST "${BASE_URL}/api/thread.begin" \
  -H "Authorization: Bearer ${TOKEN}" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "post_draft": {
      "title": "Hello Haichan",
      "body": "This is my first PoW-protected thread!",
      "attachments": [],
      "refs": []
    },
    "client_op_id": "'${OP_ID}'"
  }' | jq > challenge.json

cat challenge.json
```

**Expected Response:**
```json
{
  "challenge_id": "550e8400-e29b-41d4-a716-446655440000",
  "required_prefix_hex": "21e8",
  "challenge_version": 1,
  "op_id": "550e8400-e29b-41d4-a716-446655440001",
  "expires_at": "2024-01-01T00:01:00+00:00",
  "post_bytes_hash": "a1b2c3d4...",
  "canonical_bytes": "48433102abc123..."
}
```

## 3. Mine the Nonce

Using the WASM miner in JavaScript:
```javascript
// Load canonical_bytes and required_prefix_hex from challenge
const canonicalBytes = hexToBytes(challenge.canonical_bytes);
const requiredPrefix = challenge.required_prefix_hex;

// Mine for valid nonce
let nonce = 0;
while (nonce < 10000000) {
  const input = new Uint8Array([...canonicalBytes, ...u64ToLEBytes(nonce)]);
  const hash = await crypto.subtle.digest('SHA-256', input);
  const hashHex = Array.from(new Uint8Array(hash))
    .map(b => b.toString(16).padStart(2, '0')).join('');
  
  if (hashHex.startsWith(requiredPrefix)) {
    console.log(`Found nonce: ${nonce}, hash: ${hashHex}`);
    break;
  }
  nonce++;
}
```

Or use the Rust miner CLI (if built):
```bash
./pow-miner --canonical-bytes "$(cat challenge.json | jq -r .canonical_bytes)" \
            --required-prefix "$(cat challenge.json | jq -r .required_prefix_hex)"
```

## 4. Commit Thread with Proof

```bash
CHALLENGE_ID=$(cat challenge.json | jq -r .challenge_id)
OP_ID=$(cat challenge.json | jq -r .op_id)
NONCE=30400  # Replace with mined nonce
TIMESTAMP=$(date +%s)000  # Current timestamp in milliseconds

curl -X POST "${BASE_URL}/api/thread.commit" \
  -H "Authorization: Bearer ${TOKEN}" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "op_id": "'${OP_ID}'",
    "challenge_id": "'${CHALLENGE_ID}'",
    "post_draft": {
      "title": "Hello Haichan",
      "body": "This is my first PoW-protected thread!",
      "attachments": [],
      "refs": []
    },
    "proof": {
      "nonce_u64": '${NONCE}',
      "miner_version": 1,
      "timestamp_i64": '${TIMESTAMP}'
    }
  }' | jq
```

**Expected Response (Success):**
```json
{
  "thread_id": 42,
  "hash_hex": "21e81dddb0bccbec7d51e1636d81b17816cd8aa994a93f294fde440b7ee1477f"
}
```

**Expected Response (Failure):**
```json
{
  "error": "Invalid PoW: Hash abc123... does not start with required prefix 21e8"
}
```

## 5. Begin Reply Creation

```bash
REPLY_OP_ID=$(uuidgen)
THREAD_ID=42

curl -X POST "${BASE_URL}/api/reply.begin" \
  -H "Authorization: Bearer ${TOKEN}" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "thread_id": '${THREAD_ID}',
    "parent_id": null,
    "post_draft": {
      "title": "",
      "body": "Great thread! Here is my reply.",
      "attachments": [],
      "refs": []
    },
    "client_op_id": "'${REPLY_OP_ID}'"
  }' | jq > reply_challenge.json
```

## 6. Commit Reply

```bash
REPLY_CHALLENGE_ID=$(cat reply_challenge.json | jq -r .challenge_id)
REPLY_OP_ID=$(cat reply_challenge.json | jq -r .op_id)
REPLY_NONCE=45678  # Replace with mined nonce
REPLY_TIMESTAMP=$(date +%s)000

curl -X POST "${BASE_URL}/api/reply.commit" \
  -H "Authorization: Bearer ${TOKEN}" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "op_id": "'${REPLY_OP_ID}'",
    "challenge_id": "'${REPLY_CHALLENGE_ID}'",
    "thread_id": '${THREAD_ID}',
    "parent_id": null,
    "post_draft": {
      "title": "",
      "body": "Great thread! Here is my reply.",
      "attachments": [],
      "refs": []
    },
    "proof": {
      "nonce_u64": '${REPLY_NONCE}',
      "miner_version": 1,
      "timestamp_i64": '${REPLY_TIMESTAMP}'
    }
  }' | jq
```

**Expected Response:**
```json
{
  "post_id": 123,
  "hash_hex": "21e8abc..."
}
```

## Testing Error Cases

### Invalid Nonce
```bash
curl -X POST "${BASE_URL}/api/thread.commit" \
  -H "Authorization: Bearer ${TOKEN}" \
  -H "Content-Type: application/json" \
  -d '{
    "op_id": "'${OP_ID}'",
    "challenge_id": "'${CHALLENGE_ID}'",
    "post_draft": {...},
    "proof": {
      "nonce_u64": 999999,
      "miner_version": 1,
      "timestamp_i64": '${TIMESTAMP}'
    }
  }' | jq
```

### Mutated Draft
```bash
curl -X POST "${BASE_URL}/api/thread.commit" \
  -H "Authorization: Bearer ${TOKEN}" \
  -H "Content-Type: application/json" \
  -d '{
    "op_id": "'${OP_ID}'",
    "challenge_id": "'${CHALLENGE_ID}'",
    "post_draft": {
      "title": "Modified Title",  # Different from begin
      "body": "Modified body",
      "attachments": [],
      "refs": []
    },
    "proof": {...}
  }' | jq
```

Expected: `{"error": "Post draft mismatch"}`

### Expired Challenge
Wait >60 seconds after calling begin, then commit. Expected: `{"error": "Challenge expired"}`
