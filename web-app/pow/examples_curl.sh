#!/bin/bash
# Example API calls for Haichan PoW System v1

BASE_URL="http://localhost:8000/api"
TOKEN="your-bearer-token-here"

echo "=== Haichan PoW v1 API Examples ==="
echo

echo "1. Get PoW Parameters"
curl -X GET "$BASE_URL/pow/params" \
  -H "Content-Type: application/json" | jq .
echo
echo

echo "2. Thread Begin (Create Challenge)"
RESPONSE=$(curl -s -X POST "$BASE_URL/pow/thread/begin" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "client_op_id": "550e8400-e29b-41d4-a716-446655440000",
    "post_draft": {
      "title": "Test Thread via API",
      "body": "This is a test thread created via the PoW API",
      "attachments": [],
      "refs": []
    }
  }')

echo "$RESPONSE" | jq .
CHALLENGE_ID=$(echo "$RESPONSE" | jq -r '.challenge_id')
echo
echo "Challenge ID: $CHALLENGE_ID"
echo

echo "3. Thread Commit (Submit Proof)"
echo "Note: You need to mine a valid nonce using the WASM miner"
echo "For this example, we'll use a placeholder nonce (will fail verification)"
curl -X POST "$BASE_URL/pow/thread/commit" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d "{
    \"op_id\": \"550e8400-e29b-41d4-a716-446655440000\",
    \"challenge_id\": \"$CHALLENGE_ID\",
    \"post_draft\": {
      \"title\": \"Test Thread via API\",
      \"body\": \"This is a test thread created via the PoW API\",
      \"attachments\": [],
      \"refs\": []
    },
    \"proof\": {
      \"nonce_u64\": 12345,
      \"miner_version\": 1,
      \"timestamp_i64\": $(date +%s)
    }
  }" | jq .
echo
echo

echo "4. Reply Begin (Create Challenge for Reply)"
curl -X POST "$BASE_URL/pow/reply/begin" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "client_op_id": "550e8400-e29b-41d4-a716-446655440001",
    "thread_id": 1,
    "parent_id": null,
    "post_draft": {
      "body": "This is a reply",
      "attachments": [],
      "refs": []
    }
  }' | jq .
echo
