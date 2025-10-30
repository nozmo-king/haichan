# Haichan API Controllers

This directory contains all API controllers for the Haichan proof-of-work imageboard system.

## Controllers Overview

### AuthApiController.php
**Purpose**: Handles cryptocurrency-based authentication challenges and verification
**Key Methods**:
- `getChallenge()` - Generates cryptographic challenges for public key authentication
- Validates public keys against allowed list
- Manages user session tokens and challenge expiration

### ForumApiController.php
**Purpose**: Provides API access to forum board data and metadata
**Key Methods**:
- `getBoards()` - Returns list of all forum boards with thread counts
- `getBoardsMetadata()` - Extended board data including activity metrics and statistics
- Calculates activity scores based on recent posts and threads

### MiningTelemetryController.php
**Purpose**: Collects and logs proof-of-work mining telemetry data
**Key Methods**:
- `ingest()` - Accepts and rate-limits mining session telemetry
- Logs mining attempts, patterns, and hash data for analysis
- Rate limited to 30 requests per 5 minutes per session

### PostController.php
**Purpose**: Manages thread posts via REST API
**Key Methods**:
- `index()` - Retrieves all posts for a specific thread
- `store()` - Creates new posts (requires authentication)
- Handles post content, images, and threading relationships

### PowController.php
**Purpose**: Core proof-of-work challenge and mining system
**Key Methods**:
- `getParams()` - Returns mining parameters and difficulty settings
- `threadBegin()` - Initiates proof-of-work challenges for thread creation
- Manages SHA256-based mining with configurable difficulty prefixes
- Default difficulty: '21e8' (4-byte vanity prefix)

### ProofController.php
**Purpose**: Validates and processes proof-of-work submissions
**Key Methods**:
- `submit()` - Verifies SHA256 proof-of-work solutions
- Validates hash patterns against challenge data
- Logs proof attempts and success rates

### UserToolbarController.php
**Purpose**: Provides user-specific toolbar data and statistics  
**Key Methods**:
- `getToolbarData()` - Returns user stats, achievements, and session info
- Integrates with Bitcoin authentication system
- Provides personal mining achievements and totals

## Security Features
- All endpoints use CSRF protection
- Rate limiting on mining telemetry
- Public key validation for authentication
- SHA256-based proof-of-work verification
- Session-based Bitcoin address authentication

## Mining System Architecture
The API implements a complete proof-of-work system where:
1. Users authenticate with Bitcoin public keys
2. Challenges are generated for actions (posting, threading)
3. Clients mine SHA256 hashes with vanity prefixes
4. Proofs are validated server-side before allowing actions
5. Points are awarded based on proof difficulty and computation

## Performance Considerations
- Challenges have 60-second TTL to prevent replay attacks
- Proof verification limited to 5ms server-side processing
- Telemetry ingestion is rate-limited and async logged
- Board metadata includes cached activity calculations

This API supports a maximum of 256 elite users in the proof-of-work imageboard system.