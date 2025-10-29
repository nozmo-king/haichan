# Haichan PoW System v1

Stable proof-of-work posting system for haichan with Rust verifier, WASM miner, and Laravel API.

## Architecture

- **Verifier**: Rust library (`pow/verifier`) for canonical byte encoding and PoW verification
- **WASM Miner**: Rust WASM module (`pow/miner-wasm`) for client-side mining
- **API**: Laravel/PHP controllers (`app/Http/Controllers/Api/PowController.php`)
- **Database**: SQLite with migrations for challenges, commits, and op receipts

## PoW Algorithm (v1 "21e8 mode")

### Validity Rule
```
hex(sha256(input)).starts_with(required_prefix_hex)
```

Default prefix: `21e8` (difficulty scales by length: `0021e8`, `000021e8`, etc.)

### Canonical Bytes Encoding (v1)
```
bytes = concat(
  "HC1",                          // 3 bytes: prefix
  user_pubkey_hex,                // 66 bytes: secp256k1 pubkey
  scope,                          // 1 byte: 't' or 'r'
  thread_id (u64 LE),             // 8 bytes
  parent_id (u64 LE),             // 8 bytes
  timestamp_i64 (i64 LE),         // 8 bytes
  sha256(post_json_minified)      // 32 bytes: post hash
)
```

### Post JSON Minification
```json
{"attachments":[],"body":"...","refs":[],"title":"..."}
```
Keys sorted alphabetically, UTF-8, no extra whitespace.

### PoW Input
```
input = concat(canonical_bytes, nonce_u64_le)
digest = sha256(input)
valid iff hex(digest).starts_with(required_prefix_hex)
```

## API Endpoints

### Get PoW Parameters
```
GET /api/pow/params
```

Response:
```json
{
  "mode": "vanity_prefix",
  "default_prefix": "21e8",
  "min_miner_version": 1,
  "suggested_prefix_by_load": "21e8"
}
```

### Thread Creation Flow

#### 1. Begin Challenge
```
POST /api/pow/thread/begin
Authorization: Bearer <token>
Content-Type: application/json

{
  "client_op_id": "550e8400-e29b-41d4-a716-446655440000",
  "post_draft": {
    "title": "My First Thread",
    "body": "Hello, haichan!",
    "attachments": [],
    "refs": []
  }
}
```

Response:
```json
{
  "challenge_id": "7c9e6679-7425-40de-944b-e07fc1f90ae7",
  "required_prefix_hex": "21e8",
  "challenge_version": 1,
  "op_id": "550e8400-e29b-41d4-a716-446655440000",
  "expires_at": "2024-11-14T10:15:30Z",
  "post_bytes_hash": "a3c4f..."
}
```

#### 2. Mine PoW (Client-Side)
Use WASM miner to find valid nonce.

#### 3. Commit Thread
```
POST /api/pow/thread/commit
Authorization: Bearer <token>
Content-Type: application/json

{
  "op_id": "550e8400-e29b-41d4-a716-446655440000",
  "challenge_id": "7c9e6679-7425-40de-944b-e07fc1f90ae7",
  "post_draft": {
    "title": "My First Thread",
    "body": "Hello, haichan!",
    "attachments": [],
    "refs": []
  },
  "proof": {
    "nonce_u64": 3759,
    "miner_version": 1,
    "timestamp_i64": 1700000000
  }
}
```

Response:
```json
{
  "thread_id": 42
}
```

## Test Vectors

Golden test vectors in `pow/vectors/v1_test_vectors_solved.json`:

1. **solvable_21e8_thread**: Valid thread with nonce 3759
2. **solvable_0021e8_reply**: Valid reply with harder difficulty (nonce 3266874)
3. **negative_wrong_prefix**: Invalid - hash doesn't match prefix
4. **negative_mutated_draft**: Invalid - post draft changed
5. **negative_expired_challenge**: Invalid - challenge TTL exceeded

## Building

### Rust Verifier
```bash
cd pow/verifier
cargo test --release
cargo run --release --bin populate_vectors
```

### WASM Miner
```bash
cd pow/miner-wasm
cargo build --target wasm32-unknown-unknown --release
wasm-bindgen ../target/wasm32-unknown-unknown/release/pow_miner_wasm.wasm \
  --out-dir pkg --target web
```

### Laravel Migrations
```bash
php artisan migrate
```

## License

MIT
