# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Development Commands

### Laravel Backend (PHP)
- `composer run dev` - Start all development servers (Laravel server, queue worker, logs, and Vite)
- `composer run test` - Run PHP tests with configuration clearing
- `php artisan serve` - Start Laravel development server
- `php artisan test` - Run PHPUnit tests
- `php artisan queue:listen --tries=1` - Start queue worker
- `php artisan pail --timeout=0` - Watch Laravel logs
- `vendor/bin/pint` - Run Laravel Pint code formatter

### Frontend (JavaScript/TypeScript)
- `npm run dev` - Start Vite development server
- `npm run build` - Build production assets
- `npm run type-check` - Run TypeScript type checking
- `npm run test` - Run Jest tests
- `npm run generate-keypair [count]` - Generate secp256k1 keypairs

## Architecture Overview

### Hybrid Laravel + TypeScript Application
This is a Laravel 12 application with TypeScript components for cryptographic operations. The application implements a forum system with:

- **Secp256k1 Public Key Authentication**: Users authenticate using cryptographic signatures rather than passwords
- **Subscription-based Access**: Stripe integration for paid subscriptions and friend code system
- **Forum Structure**: Boards → Threads → Posts hierarchy
- **Proof of Work System**: Mining-like system for certain operations

### Key Directory Structure
```
app/
├── Http/Controllers/          # Laravel controllers (web + API)
├── Models/                    # Eloquent models (User, Post, Thread, etc.)
├── Services/                  # Business logic services
├── Console/Commands/          # Artisan commands
└── Http/Middleware/           # Authentication and validation middleware

src/crypto/                    # TypeScript cryptographic utilities
resources/                     # Laravel views and frontend assets
routes/                        # Web, API, and console routes
database/migrations/           # Database schema
```

### Authentication Flow
The application uses a custom authentication system based on secp256k1 public key cryptography:
1. Users generate keypairs using the TypeScript utility
2. Public keys are stored in `allowed_public_keys` table
3. Authentication happens via cryptographic signature verification
4. Subscription status gates access to forum features

### Database Models
- **User**: Authenticated via public key, linked to subscriptions
- **Board**: Top-level forum categories
- **Thread**: Discussion topics within boards
- **Post**: Individual messages (can be replies to other posts)
- **Subscription/Payment**: Stripe billing integration
- **FriendCode**: Temporary access codes for new users
- **ProofOfWork/Mining**: Custom PoW system

### Payment System
- Stripe integration with webhook handling
- Subscription plans managed through admin interface
- Friend codes provide temporary access without payment
- Commands handle payment status checks and subscription renewals

### Development Environment
- Vite for frontend asset bundling
- Concurrently runs multiple services: Laravel server, queue worker, logs, and Vite
- Uses SQLite by default for development
- TypeScript with strict type checking enabled