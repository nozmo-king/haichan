# haichan - Proof-of-Work (PoW) Implementation

This document outlines the setup, building, and testing procedures for the Proof-of-Work (PoW) implementation within the haichan project.

## Project Overview

This project integrates a custom Proof-of-Work system for posting content, leveraging Rust for the core PoW logic (verifier and WebAssembly miner) and Laravel/PHP for the API backend.

## Getting Started

### Prerequisites

*   PHP (8.2+)
*   Composer
*   Node.js & npm
*   Rust & Cargo
*   wasm-pack

### Installation

1.  **Clone the repository:**
    ```bash
    git clone https://github.com/your-repo/haichan.git
    cd haichan/web-app
    ```

2.  **PHP Dependencies:**
    ```bash
    composer install
    ```

3.  **JavaScript Dependencies:**
    ```bash
    npm install
    ```

4.  **Rust Toolchain & wasm-pack:**
    If you don't have Rust and Cargo installed, follow the instructions on [rust-lang.org](https://www.rust-lang.org/tools/install).
    Then, install `wasm-pack`:
    ```bash
    curl https://rustwasm.github.io/wasm-pack/installer/init.sh -sSf | sh
    ```

5.  **Environment Setup:**
    ```bash
    cp .env.example .env
    php artisan key:generate
    ```
    Configure your database in `.env`. By default, SQLite is used, but you can switch to MySQL/MariaDB.

6.  **Run Migrations:**
    ```bash
    php artisan migrate
    ```

## Building and Running

### Development Server

To start the Laravel development server, queue listener, log tailer, and Vite development server:

```bash
composer dev
```

### Building WebAssembly Miner

To build the WebAssembly module for the PoW miner:

```bash
cd pow/miner-wasm
wasm-pack build --target web --out-dir ../../public/pkg
cd ../../
```

This will place the `miner-wasm.wasm` and `miner-wasm.js` files in `public/pkg`.

## Testing

### Rust Tests

To run the Rust tests for the `pow-verifier` and `pow-miner-wasm` crates:

```bash
cd pow
cargo test --workspace
cd ../
```

### PHPUnit Tests

To run the PHPUnit tests for the Laravel application:

```bash
php artisan test
```

## API Endpoints (cURL Examples)

Assuming your Laravel development server is running on `http://localhost:8000`.

### 1. Get PoW Parameters

```bash
curl -X GET http://localhost:8000/api/pow/params
```

Expected Output:
```json
{
    "mode": "vanity_prefix",
    "default_prefix": "21e8",
    "min_miner_version": 1,
    "suggested_prefix_by_load": "21e8"
}
```

### 2. Begin Thread PoW Challenge

```bash
curl -X POST http://localhost:8000/api/thread/begin \
     -H "Content-Type: application/json" \
     -H "X-Pubkey: <YOUR_PUBLIC_KEY_HEX>" \
     -d '{ "post_draft": { "title": "My New Thread", "body": "Content of my new thread.", "attachments": [], "refs": [] }, "client_op_id": "$(uuidgen)" }'
```

Replace `<YOUR_PUBLIC_KEY_HEX>` with an actual public key hex string (e.g., `02abcdef1234567890...`).

Expected Output:
```json
{
    "challenge_id": "<UUID>",
    "required_prefix_hex": "21e8",
    "challenge_version": 1,
    "op_id": "<CLIENT_OP_ID>",
    "expires_at": <TIMESTAMP>,
    "post_bytes_hash": "<HEX_HASH>"
}
```

### 3. Commit Thread PoW Solution

After receiving a challenge, you would use the `miner-wasm` (or a custom miner) to find a `nonce_u64` that satisfies the `required_prefix_hex`.

```bash
curl -X POST http://localhost:8000/api/thread/commit \
     -H "Content-Type: application/json" \
     -H "X-Pubkey: <YOUR_PUBLIC_KEY_HEX>" \
     -d '{ "op_id": "<CLIENT_OP_ID_FROM_BEGIN>", "challenge_id": "<CHALLENGE_ID_FROM_BEGIN>", "post_draft": { "title": "My New Thread", "body": "Content of my new thread.", "attachments": [], "refs": [] }, "proof": { "nonce_u64": <FOUND_NONCE>, "miner_version": 1, "timestamp_i64": <CURRENT_TIMESTAMP> } }'
```

Replace placeholders with actual values from the `begin` response and your PoW solution.

Expected Output:
```json
{
    "thread_id": "<UUID_OF_NEW_THREAD>"
}
```

### 4. Begin Reply PoW Challenge (Symmetrical to Thread)

```bash
curl -X POST http://localhost:8000/api/reply/begin \
     -H "Content-Type: application/json" \
     -H "X-Pubkey: <YOUR_PUBLIC_KEY_HEX>" \
     -d '{ "post_draft": { "title": "Re: My New Thread", "body": "My reply content.", "attachments": [], "refs": [] }, "client_op_id": "$(uuidgen)", "thread_id": "<EXISTING_THREAD_UUID>", "parent_id": "<EXISTING_POST_UUID>" }'
```

### 5. Commit Reply PoW Solution (Symmetrical to Thread)

```bash
curl -X POST http://localhost:8000/api/reply/commit \
     -H "Content-Type: application/json" \
     -H "X-Pubkey: <YOUR_PUBLIC_KEY_HEX>" \
     -d '{ "op_id": "<CLIENT_OP_ID_FROM_BEGIN>", "challenge_id": "<CHALLENGE_ID_FROM_BEGIN>", "post_draft": { "title": "Re: My New Thread", "body": "My reply content.", "attachments": [], "refs": [] }, "proof": { "nonce_u64": <FOUND_NONCE>, "miner_version": 1, "timestamp_i64": <CURRENT_TIMESTAMP> } }'
```