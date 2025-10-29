# Haichan Stable PoW Posting System

Proof-of-Work based posting system for Haichan using SHA-256 vanity prefix mining.

## Architecture

- **Language**: Rust (verifier lib + WASM miner), Laravel/PHP (API), SQLite
- **PoW Algorithm**: SHA-256
- **Validity Rule (v1 "21e8 mode")**: `hex(sha256(input)).starts_with(required_prefix_hex)`
- **Default Prefix**: `21e8` (difficulty scales by prefix length: `0021e8`, `000021e8`, etc.)
- **Challenge TTL**: 60 seconds
- **Server Verify Budget**: ≤5ms

## Data Model

### Tables

#### `users`
- `id`: Primary key
- `pubkey_hex`: Unique secp256k1 public key (66 chars)
- `created_at`: Timestamp

#### `posts`
- `id`: Primary key
- `thread_id`: Nullable, references thread
- `parent_id`: Nullable, references parent post
- `author_pubkey_hex`: Author's public key
- `title`: Post title
- `body`: Post content
- `attachments_json`: JSON array of attachments
- `created_at`: Timestamp

#### `pow_challenges`
- `id`: UUID primary key
- `user_pubkey_hex`: Challenge owner
- `scope`: ENUM('thread', 'reply')
- `thread_id`: Nullable
- `parent_id`: Nullable
- `post_bytes_hash`: BLOB(32) - SHA-256 of post data
- `required_prefix_hex`: Required hash prefix
- `challenge_version`: INT (currently 1)
- `expires_at`: Expiration timestamp
- `created_at`: Timestamp

#### `pow_commits`
- `id`: UUID primary key
- `challenge_id`: Foreign key to challenges
- `nonce_u64`: Submitted nonce
- `miner_version`: Miner version number
- `timestamp_i64`: Mining timestamp
- `solved_hash_hex`: Resulting hash (CHAR 64)
- `accepted`: BOOLEAN
- `reject_reason`: TEXT, nullable
- `solve_time_ms`: INT, nullable
- `created_at`: Timestamp

#### `op_receipts`
- `client_op_id`: UUID primary key (idempotency key)
- `result_json`: Cached response TEXT
- `created_at`: Timestamp

## Canonical Byte Encoding (v1)

```
bytes = concat(
  prefix="HC1",                    // 3 bytes
  user_pubkey_hex,                 // 66 bytes (hex string)
  scope,                           // 1 byte: 't' or 'r'
  thread_id|0,                     // 8 bytes: u64 little-endian
  parent_id|0,                     // 8 bytes: u64 little-endian
  timestamp_i64,                   // 8 bytes: i64 little-endian
  sha256(post_json_minified)       // 32 bytes
)
```

### Post JSON Minified Format

```json
{"attachments":[],"body":"...","refs":[],"title":"..."}
```

Keys are sorted alphabetically, UTF-8 encoded, with no extra whitespace.

## PoW Input (v1)

```
input = concat(canonical_bytes, extra_data="", nonce_u64_le)
digest = sha256(input)
valid iff hex(digest).starts_with(required_prefix_hex)
```

## HTTP API

### Public Endpoints

#### `GET /api/pow.params`

Returns current PoW parameters.

**Response:**
```json
{
  "mode": "vanity_prefix",
  "default_prefix": "21e8",
  "min_miner_version": 1,
  "suggested_prefix_by_load": "21e8"
}
```

### Protected Endpoints (requires auth:sanctum)

#### `POST /api/thread.begin`

Initiate thread creation and receive PoW challenge.

**Request:**
```json
{
  "post_draft": {
    "title": "Thread Title",
    "body": "Thread content",
    "attachments": [],
    "refs": []
  },
  "client_op_id": "uuid-v4"
}
```

**Response:**
```json
{
  "challenge_id": "uuid",
  "required_prefix_hex": "21e8",
  "challenge_version": 1,
  "op_id": "uuid",
  "expires_at": "2024-01-01T00:01:00+00:00",
  "post_bytes_hash": "hex",
  "canonical_bytes": "hex"
}
```

#### `POST /api/thread.commit`

Submit PoW proof and create thread.

**Request:**
```json
{
  "op_id": "uuid",
  "challenge_id": "uuid",
  "post_draft": { /* same as begin */ },
  "proof": {
    "nonce_u64": 12345,
    "miner_version": 1,
    "timestamp_i64": 1700000000000
  }
}
```

**Response:**
```json
{
  "thread_id": 42,
  "hash_hex": "21e8abc..."
}
```

#### `POST /api/reply.begin`

Initiate reply creation (symmetric to thread.begin).

**Request:**
```json
{
  "thread_id": 42,
  "parent_id": 100,
  "post_draft": { /* ... */ },
  "client_op_id": "uuid"
}
```

#### `POST /api/reply.commit`

Submit reply proof (symmetric to thread.commit).

## Rules

1. **Server Authority**: Server builds canonical bytes and `post_bytes_hash`; client must echo identical `post_draft` between begin and commit or request is rejected.

2. **Idempotency**: Use `client_op_id` for idempotent operations.

3. **Difficulty Control**: Server alone chooses `required_prefix_hex` per user/load; client never infers difficulty.

4. **Telemetry**: System logs `miner_version`, `solve_time_ms`, `reject_reason`, and `solved_hash_hex`.

## Test Vectors

Five golden test vectors are included in `/pow/vectors/test_vectors_v1.json`:

1. **valid_thread_21e8**: Solvable case with prefix '21e8'
2. **valid_reply_0021e8**: Solvable case with harder prefix '0021e8'
3. **invalid_wrong_prefix**: Nonce produces wrong prefix
4. **invalid_mutated_draft**: Post draft modified between begin/commit
5. **invalid_expired_challenge**: Challenge TTL exceeded

## Repository Layout

```
/pow/
  verifier/           # Rust library + tests
    src/
      lib.rs
      encoder.rs
      verifier.rs
    Cargo.toml
  miner-wasm/         # Rust + wasm-bindgen
    src/
      lib.rs
    Cargo.toml
  vectors/            # JSON test vectors
    test_vectors_v1.json
/app/Http/Controllers/
  PowController.php
/routes/
  api.php
/database/migrations/
  2025_10_22_create_pow_tables.php
/tests/Feature/
  PowSystemTest.php
```

## Building

### Rust Verifier

```bash
cd pow/verifier
cargo test --release
cargo run --release --bin generate_vectors
```

### WASM Miner

```bash
cd pow/miner-wasm
wasm-pack build --target web --release
```

Output: `pkg/haichan_miner_wasm_bg.wasm`

### PHP Tests

```bash
php artisan migrate:fresh
php artisan test --filter PowSystemTest
```

## CI Gates

GitHub Actions workflow runs:
- Rust tests (verifier + WASM)
- PHPUnit tests
- Vector validation

Fails on any vector mismatch.

## Usage Example (cURL)

```bash
# 1. Get params
curl -X GET http://localhost/api/pow.params

# 2. Begin thread
curl -X POST http://localhost/api/thread.begin \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "post_draft": {
      "title": "Hello",
      "body": "World",
      "attachments": [],
      "refs": []
    },
    "client_op_id": "'$(uuidgen)'"
  }'

# 3. Mine nonce (use WASM miner)

# 4. Commit
curl -X POST http://localhost/api/thread.commit \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "op_id": "$OP_ID",
    "challenge_id": "$CHALLENGE_ID",
    "post_draft": { ... },
    "proof": {
      "nonce_u64": 12345,
      "miner_version": 1,
      "timestamp_i64": 1700000000000
    }
  }'
```

## Versioning

**V1 Compatibility**: Never break v1. Add new fields only with defaults. Future versions (v2, v3...) can add fields to canonical bytes and be validated separately.
