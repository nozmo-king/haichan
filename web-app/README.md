# Haichan PoW Implementation

This document outlines the Proof-of-Work (PoW) implementation for Haichan, including the Rust-based verifier and WASM miner, Laravel API, and associated tooling.

## Table of Contents

- [Haichan PoW Implementation](#haichan-pow-implementation)
  - [Table of Contents](#table-of-contents)
  - [Build and Run Instructions](#build-and-run-instructions)
    - [Rust Toolchain and wasm-pack](#rust-toolchain-and-wasm-pack)
    - [Laravel Application](#laravel-application)
  - [Running Tests](#running-tests)
    - [Rust Tests](#rust-tests)
    - [PHPUnit Tests](#phpunit-tests)
  - [API Examples (cURL)](#api-examples-curl)
    - [GET /api/pow.params](#get-apipowparams)
    - [POST /api/thread.begin](#post-apithreadbegin)
    - [POST /api/thread.commit](#post-apithreadcommit)
    - [POST /api/reply.begin](#post-apireplybegin)
    - [POST /api/reply.commit](#post-apireplycommit)
  - [Golden Test Vectors](#golden-test-vectors)
  - [Environment Variables](#environment-variables)

## Build and Run Instructions

### Rust Toolchain and wasm-pack

To build the Rust verifier and WASM miner, you need to have the Rust toolchain and `wasm-pack` installed.

1.  **Install Rust:**

    ```bash
    curl --proto '=https' --tlsv1.2 -sSf https://sh.rustup.rs | sh
    source $HOME/.cargo/env
    rustup target add wasm32-unknown-unknown
    ```

2.  **Install wasm-pack:**

    ```bash
    curl https://rustwasm.github.io/wasm-pack/installer/init.sh -sSf | sh
    ```

3.  **Build the WASM miner:**

    ```bash
    cd pow/miner-wasm
    wasm-pack build --target web
    cd ../..
    ```

### Laravel Application

1.  **Install Composer dependencies:**

    ```bash
    composer install
    ```

2.  **Copy environment file:**

    ```bash
    cp .env.example .env
    ```

3.  **Generate application key:**

    ```bash
    php artisan key:generate
    ```

4.  **Run database migrations:**

    ```bash
    php artisan migrate
    ```

5.  **Start the Laravel development server:**

    ```bash
    php artisan serve
    ```

    The application will be available at `http://127.0.0.1:8000`.

## Running Tests

### Rust Tests

To run the tests for the Rust verifier and WASM miner:

```bash
cd pow/verifier && cargo test
cd ../miner-wasm && wasm-pack test --headless --chrome
cd ../..
```

### PHPUnit Tests

To run the PHPUnit tests for the Laravel application:

```bash
php artisan test
```

## API Examples (cURL)

Assume `BASE_URL=http://127.0.0.1:8000/api` and `USER_PUBKEY_HEX` is a valid public key.

### GET /api/pow.params

```bash
curl -X GET "${BASE_URL}/pow.params"
```

### POST /api/thread.begin

```bash
curl -X POST "${BASE_URL}/thread.begin" \
     -H "Content-Type: application/json" \
     -d '{ "post_draft": { "title": "My New Thread", "body": "This is the content.", "attachments": [], "refs": [] }, "client_op_id": "$(uuidgen)", "user_pubkey_hex": "<YOUR_PUBKEY_HEX>", "timestamp_i64": $(date +%s) }'
```

### POST /api/thread.commit

```bash
# Replace with actual values from thread.begin response and miner output
CHALLENGE_ID="<CHALLENGE_ID_FROM_BEGIN>"
OP_ID="<OP_ID_FROM_BEGIN>"
NONCE_U64=<NONCE_FROM_MINER>
MINER_VERSION=1
TIMESTAMP_I64=$(date +%s)
SOLVED_HASH_HEX="<SOLVED_HASH_FROM_MINER>"

curl -X POST "${BASE_URL}/thread.commit" \
     -H "Content-Type: application/json" \
     -d '{ "op_id": "${OP_ID}", "challenge_id": "${CHALLENGE_ID}", "post_draft": { "title": "My New Thread", "body": "This is the content.", "attachments": [], "refs": [] }, "proof": { "nonce_u64": ${NONCE_U64}, "miner_version": ${MINER_VERSION}, "timestamp_i64": ${TIMESTAMP_I64} }, "user_pubkey_hex": "<YOUR_PUBKEY_HEX>" }'
```

### POST /api/reply.begin

```bash
# Replace with an actual thread_id
THREAD_ID="<EXISTING_THREAD_ID>"

curl -X POST "${BASE_URL}/reply.begin" \
     -H "Content-Type: application/json" \
     -d '{ "post_draft": { "body": "This is a reply.", "attachments": [], "refs": [] }, "client_op_id": "$(uuidgen)", "user_pubkey_hex": "<YOUR_PUBKEY_HEX>", "thread_id": "${THREAD_ID}", "parent_id": null, "timestamp_i64": $(date +%s) }'
```

### POST /api/reply.commit

```bash
# Replace with actual values from reply.begin response and miner output
CHALLENGE_ID="<CHALLENGE_ID_FROM_BEGIN>"
OP_ID="<OP_ID_FROM_BEGIN>"
NONCE_U64=<NONCE_FROM_MINER>
MINER_VERSION=1
TIMESTAMP_I64=$(date +%s)
SOLVED_HASH_HEX="<SOLVED_HASH_FROM_MINER>"
THREAD_ID="<EXISTING_THREAD_ID>"

curl -X POST "${BASE_URL}/reply.commit" \
     -H "Content-Type: application/json" \
     -d '{ "op_id": "${OP_ID}", "challenge_id": "${CHALLENGE_ID}", "post_draft": { "body": "This is a reply.", "attachments": [], "refs": [] }, "proof": { "nonce_u64": ${NONCE_U64}, "miner_version": ${MINER_VERSION}, "timestamp_i64": ${TIMESTAMP_I64} }, "user_pubkey_hex": "<YOUR_PUBKEY_HEX>", "thread_id": "${THREAD_ID}", "parent_id": null }'
```

## Golden Test Vectors

The golden test vectors are located in `pow/vectors/golden_vectors.json`. These vectors are used to verify the correctness of the PoW implementation.

## Environment Variables

See `.env.example` for required environment variables.
