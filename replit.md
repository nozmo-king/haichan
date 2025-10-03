# Haichan - Cryptographically-Secured Imageboard

## Overview
A modern, cryptographically-secured imageboard built with Laravel 12, TypeScript, and Rust clients. Features a unique proof-of-work mining system and secp256k1 public key authentication for secure, decentralized user identity.

## Project Status
- **Environment**: Replit Development
- **Primary Stack**: Laravel 12 (PHP 8.3) + Vite + TypeScript
- **Database**: SQLite (development)
- **Port**: 5000 (Frontend + Backend)
- **Last Updated**: October 3, 2025

## Recent Changes
- **Oct 3, 2025**: Initial Replit setup completed
  - Installed PHP 8.3 and Node.js 20
  - Configured Laravel environment and database
  - Fixed duplicate migration issue in proof_of_works table
  - Configured Vite for Replit proxy compatibility
  - Set up development workflow on port 5000
  - Configured deployment settings for production

## Architecture

### Backend (Laravel 12)
- **Framework**: Laravel 12 with PHP 8.3
- **Database**: SQLite (development), supports MySQL/PostgreSQL (production)
- **Authentication**: secp256k1 public key cryptography
- **Queue System**: Database-backed queues for background processing
- **Payment**: Stripe integration for subscriptions

### Frontend (TypeScript + Vite)
- **Build Tool**: Vite 6.0
- **Language**: TypeScript 5.0
- **Crypto**: Noble secp256k1 for cryptographic operations
- **Mining**: Browser-based SHA-256 proof-of-work system
- **Styling**: Tailwind CSS 4.1

### Additional Clients
- **TUI Client**: Rust-based terminal interface (in tui-client/)
- **Rust Library**: Shared client functionality (in rust-client/)

## Key Features

### Cryptographic Authentication
- secp256k1 elliptic curve public key authentication
- No passwords stored on server
- Message signing with private keys (client-side only)
- Signature verification for authentication

### Proof-of-Work Mining
- Browser-based SHA-256 mining
- Multiple difficulty levels (IDLE, ACTIVE, HYPER modes)
- Vanity hash patterns (21e8, 21e800, etc.)
- Rarity system from COMMON to LEGENDARY
- Mining contributes to thread/user rankings

### Forum Structure
- Boards → Threads → Posts hierarchy
- Nested reply system with threading
- Image uploads with hash verification
- Real-time mining integration

## Development Setup

### Prerequisites
- PHP 8.3+ (installed)
- Node.js 20+ (installed)
- Composer (installed)
- SQLite (included with PHP)

### Running Locally
The project uses a single command to run all services:
```bash
cd web-app && composer run dev
```

This starts:
- Laravel dev server (port 5000)
- Queue worker for background jobs
- Log viewer (Laravel Pail)
- Vite dev server (port 5173 for HMR)

### Database
- Location: `web-app/database/database.sqlite`
- Migrations are in `web-app/database/migrations/`
- Run migrations: `cd web-app && php artisan migrate`

### Environment Configuration
- Config file: `web-app/.env`
- App key is auto-generated
- SQLite database is auto-created
- Default settings work for development

## Important Replit Configuration

### Vite Configuration
The Vite config (`web-app/vite.config.js`) is configured to work with Replit's proxy:
- Host: `0.0.0.0`
- HMR: localhost
- Port: 5173

### Laravel Server
- Configured to run on `0.0.0.0:5000`
- Accessible via Replit's webview

### Workflow
- Single workflow: "Server"
- Command: `cd web-app && composer run dev`
- Runs all services concurrently

## Deployment Configuration

### Build Process
1. Install production dependencies: `composer install --no-dev --optimize-autoloader`
2. Install npm packages: `npm ci`
3. Build frontend assets: `npm run build`

### Production Runtime
1. Cache configuration: `php artisan config:cache`
2. Cache routes: `php artisan route:cache`
3. Cache views: `php artisan view:cache`
4. Run migrations: `php artisan migrate --force`
5. Start server on port 5000
6. Run queue worker in background

### Deployment Type
- **VM deployment**: Required for stateful features (queue workers, mining sessions)

## API Endpoints

### Authentication
- `POST /api/auth/challenge` - Get authentication challenge
- `POST /api/auth/verify` - Verify signed challenge

### Mining
- `POST /api/submit-proof` - Submit proof-of-work
- `GET /api/mining-stats` - Get mining statistics
- `POST /api/start-mining-session` - Start mining session
- `POST /api/end-mining-session` - End mining session

### Forum
- `GET /api/boards` - List all boards
- `GET /api/boards/{board}/threads` - Get board threads
- `POST /api/boards/{board}/threads` - Create thread
- `POST /api/threads/{thread}/posts` - Create post

## Database Models

### Core Models
- **User**: Public key auth, subscriptions
- **Board**: Forum categories
- **Thread**: Discussion topics
- **Post**: Messages and replies
- **ProofOfWork**: Mining submissions
- **MiningSession**: Mining statistics

### Authentication
- **BitcoinAuth**: User authentication records
- **AllowedPublicKeys**: Whitelisted public keys

### Payment
- **Subscription**: User subscription records
- **Payment**: Stripe payment tracking
- **FriendCode**: Temporary access codes

## File Structure

```
├── web-app/                    # Main Laravel application
│   ├── app/                    # Application code
│   │   ├── Http/Controllers/   # Controllers
│   │   ├── Models/            # Eloquent models
│   │   └── Services/          # Business logic
│   ├── resources/             # Frontend resources
│   │   ├── views/             # Blade templates
│   │   ├── js/                # TypeScript/JavaScript
│   │   └── css/               # Stylesheets
│   ├── database/              # Database files
│   │   ├── migrations/        # Schema migrations
│   │   └── database.sqlite    # SQLite database
│   ├── public/                # Public assets
│   ├── routes/                # Route definitions
│   └── src/crypto/            # TypeScript crypto utilities
├── tui-client/                # Rust TUI client
├── rust-client/               # Rust client library
└── README.md                  # Project documentation
```

## Known Issues & Fixes

### Migration Conflict (Fixed)
- **Issue**: `2025_09_26_123400_add_user_id_to_proof_of_works_table` tried to add duplicate column
- **Fix**: Added check for existing column before adding
- **Status**: Resolved

## Commands Reference

### Laravel
- `php artisan serve --host=0.0.0.0 --port=5000` - Start dev server
- `php artisan migrate` - Run database migrations
- `php artisan queue:listen` - Start queue worker
- `php artisan pail` - View application logs
- `php artisan test` - Run tests
- `vendor/bin/pint` - Format PHP code

### Frontend
- `npm run dev` - Start Vite dev server
- `npm run build` - Build for production
- `npm run type-check` - Check TypeScript types
- `npm run generate-keypair [count]` - Generate secp256k1 keypairs

### Rust
- `cd tui-client && cargo run` - Run TUI client
- `cargo build --release` - Build optimized binary
- `cargo test` - Run tests

## Security Notes

- Private keys are NEVER stored on the server
- All cryptographic operations use industry-standard secp256k1
- Message signing prevents replay attacks
- Server validates all proof-of-work submissions
- Rate limiting on API endpoints
- CSRF protection enabled
- Input validation on all user inputs

## User Preferences
None specified yet.

## Next Steps
- Application is ready for development
- Consider adding seed data for testing
- Review and customize Stripe integration if needed
- Configure production database when deploying
