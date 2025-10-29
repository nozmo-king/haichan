#!/bin/bash

# HaiChan PoW System API Examples
# Usage: ./api-examples.sh [BASE_URL]

BASE_URL="${1:-http://localhost:8000}"
TOKEN="your-api-token-here"  # Replace with actual token
CONTENT_TYPE="Content-Type: application/json"
AUTH_HEADER="Authorization: Bearer $TOKEN"

echo "🚀 HaiChan PoW API Examples"
echo "Base URL: $BASE_URL"
echo

# Generate UUIDs for examples
CLIENT_OP_ID=$(uuidgen 2>/dev/null || echo "$(date +%s)-$(($RANDOM))")
THREAD_ID=42
PARENT_ID=100

echo "=== 1. Get PoW Parameters ==="
echo "GET $BASE_URL/api/pow.params"
echo
curl -X GET "$BASE_URL/api/pow.params" \
  -H "$CONTENT_TYPE" \
  -w "\nStatus: %{http_code}\n\n"

echo "=== 2. Thread Creation Flow ==="
echo
echo "--- 2a. Begin Thread Creation ---"
echo "POST $BASE_URL/api/thread.begin"
echo
BEGIN_RESPONSE=$(curl -s -X POST "$BASE_URL/api/thread.begin" \
  -H "$AUTH_HEADER" \
  -H "$CONTENT_TYPE" \
  -d '{
    "post_draft": {
      "title": "Hello HaiChan!",
      "body": "This is a test thread created via PoW mining.",
      "attachments": [],
      "refs": []
    },
    "client_op_id": "'$CLIENT_OP_ID'"
  }')

echo "Response: $BEGIN_RESPONSE"
echo

# Extract values from response (requires jq)
if command -v jq &> /dev/null; then
    CHALLENGE_ID=$(echo "$BEGIN_RESPONSE" | jq -r '.challenge_id // empty')
    OP_ID=$(echo "$BEGIN_RESPONSE" | jq -r '.op_id // empty')
    REQUIRED_PREFIX=$(echo "$BEGIN_RESPONSE" | jq -r '.required_prefix_hex // empty')
    CANONICAL_BYTES=$(echo "$BEGIN_RESPONSE" | jq -r '.canonical_bytes // empty')
    
    echo "Extracted values:"
    echo "  Challenge ID: $CHALLENGE_ID"
    echo "  Op ID: $OP_ID"
    echo "  Required Prefix: $REQUIRED_PREFIX"
    echo "  Canonical Bytes: $CANONICAL_BYTES"
    echo
else
    echo "⚠️  jq not found - manual extraction needed"
    CHALLENGE_ID="example-challenge-id"
    OP_ID="example-op-id"
    REQUIRED_PREFIX="21e8"
fi

echo "--- 2b. Mine PoW (use WASM miner or external tool) ---"
echo "This step requires mining a nonce such that:"
echo "  sha256(canonical_bytes + nonce_u64_le).starts_with('$REQUIRED_PREFIX')"
echo "  Example nonce: 29942 (from test vectors)"
echo

echo "--- 2c. Commit Thread ---"
echo "POST $BASE_URL/api/thread.commit"
echo
curl -X POST "$BASE_URL/api/thread.commit" \
  -H "$AUTH_HEADER" \
  -H "$CONTENT_TYPE" \
  -d '{
    "op_id": "'$OP_ID'",
    "challenge_id": "'$CHALLENGE_ID'",
    "post_draft": {
      "title": "Hello HaiChan!",
      "body": "This is a test thread created via PoW mining.",
      "attachments": [],
      "refs": []
    },
    "proof": {
      "nonce_u64": 29942,
      "miner_version": 1,
      "timestamp_i64": 1640995200000
    }
  }' \
  -w "\nStatus: %{http_code}\n\n"

echo "=== 3. Reply Creation Flow ==="
echo
echo "--- 3a. Begin Reply Creation ---"
echo "POST $BASE_URL/api/reply.begin"
echo
REPLY_CLIENT_OP_ID=$(uuidgen 2>/dev/null || echo "reply-$(date +%s)")
curl -X POST "$BASE_URL/api/reply.begin" \
  -H "$AUTH_HEADER" \
  -H "$CONTENT_TYPE" \
  -d '{
    "thread_id": '$THREAD_ID',
    "parent_id": '$PARENT_ID',
    "post_draft": {
      "title": "",
      "body": "This is a reply with PoW proof.",
      "attachments": ["image.jpg"],
      "refs": [">>1", ">>2"]
    },
    "client_op_id": "'$REPLY_CLIENT_OP_ID'"
  }' \
  -w "\nStatus: %{http_code}\n\n"

echo "--- 3b. Commit Reply (example with different nonce) ---"
echo "POST $BASE_URL/api/reply.commit"
echo
curl -X POST "$BASE_URL/api/reply.commit" \
  -H "$AUTH_HEADER" \
  -H "$CONTENT_TYPE" \
  -d '{
    "op_id": "example-reply-op-id",
    "challenge_id": "example-reply-challenge-id",
    "post_draft": {
      "title": "",
      "body": "This is a reply with PoW proof.",
      "attachments": ["image.jpg"],
      "refs": [">>1", ">>2"]
    },
    "proof": {
      "nonce_u64": 354,
      "miner_version": 1,
      "timestamp_i64": 1641000000000
    }
  }' \
  -w "\nStatus: %{http_code}\n\n"

echo "=== Error Scenarios ==="
echo
echo "--- Invalid Proof (wrong nonce) ---"
curl -X POST "$BASE_URL/api/thread.commit" \
  -H "$AUTH_HEADER" \
  -H "$CONTENT_TYPE" \
  -d '{
    "op_id": "'$OP_ID'",
    "challenge_id": "'$CHALLENGE_ID'",
    "post_draft": {
      "title": "Hello HaiChan!",
      "body": "This is a test thread created via PoW mining.",
      "attachments": [],
      "refs": []
    },
    "proof": {
      "nonce_u64": 999999,
      "miner_version": 1,
      "timestamp_i64": 1640995200000
    }
  }' \
  -w "\nStatus: %{http_code}\n\n"

echo "--- Mutated Post Draft ---"
curl -X POST "$BASE_URL/api/thread.commit" \
  -H "$AUTH_HEADER" \
  -H "$CONTENT_TYPE" \
  -d '{
    "op_id": "'$OP_ID'",
    "challenge_id": "'$CHALLENGE_ID'",
    "post_draft": {
      "title": "Modified Title",
      "body": "Modified content - this should fail",
      "attachments": [],
      "refs": []
    },
    "proof": {
      "nonce_u64": 123456,
      "miner_version": 1,
      "timestamp_i64": 1640995200000
    }
  }' \
  -w "\nStatus: %{http_code}\n\n"

echo "✅ API examples completed"
echo
echo "💡 Tips:"
echo "- Replace TOKEN variable with your actual API token"
echo "- Use the WASM miner to find valid nonces"
echo "- Check test vectors for known valid proof examples"
echo "- Monitor server logs for detailed error messages"