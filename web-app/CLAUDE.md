# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Development Commands

### Laravel Backend (PHP)
- `composer run dev` - Start all development servers (Laravel server, queue worker, logs, and Vite)
- `composer run test` - Run PHP tests with configuration clearing
- `php artisan serve` - Start Laravel development server on 0.0.0.0:8000
- `php artisan test` - Run PHPUnit tests
- `php artisan queue:listen --tries=1` - Start queue worker
- `php artisan pail --timeout=0` - Watch Laravel logs
- `vendor/bin/pint` - Run Laravel Pint code formatter

### Frontend (JavaScript/TypeScript)
- `npm run dev` - Start Vite development server
- `npm run build` - Build production assets
- `npm run type-check` - Run TypeScript type checking
- `npm run generate-keypair [count]` - Generate secp256k1 keypairs using tsx

## Architecture Overview

### Hybrid Laravel + TypeScript Application
This is a Laravel 12 application with TypeScript components for cryptographic operations. The application implements a forum system with:

- **Secp256k1 Public Key Authentication**: Users authenticate using cryptographic signatures rather than passwords
- **Subscription-based Access**: Stripe integration for paid subscriptions and friend code system
- **Forum Structure**: Boards → Threads → Posts hierarchy with nested replies
- **Proof of Work System**: Mining-like system for certain operations
- **Image Library System**: File upload and management for forum attachments
- **Simple Mining System**: Hover-based targeting system for mining operations

### Key Directory Structure
```
app/
├── Http/Controllers/          # Laravel controllers (web + API)
│   ├── Api/                  # API controllers for mobile/external access
│   ├── ForumController.php   # Main forum functionality
│   ├── ImageLibraryController.php  # Image upload/management
│   └── ProofOfWorkController.php   # Mining system
├── Models/                    # Eloquent models
│   ├── Board.php             # Forum boards with shifting stats
│   ├── Post.php              # Posts with image attachments
│   ├── Thread.php            # Discussion threads
│   ├── User.php              # Users with soft deletes
│   └── ImageLibrary.php      # Image file management
├── Services/                  # Business logic services
├── View/Composers/           # View composers for global data
├── Console/Commands/          # Artisan commands
└── Http/Middleware/           # Authentication and validation middleware

src/crypto/                    # TypeScript cryptographic utilities
resources/                     # Laravel views and frontend assets
├── js/simple-mining.js       # Frontend mining interface
└── views/                    # Blade templates
routes/                        # Web, API, and console routes
database/migrations/           # Database schema including recent additions
```

### Authentication Flow
The application uses a custom authentication system based on secp256k1 public key cryptography:
1. Users generate keypairs using the TypeScript utility (`npm run generate-keypair`)
2. Public keys are stored in `allowed_public_keys` table
3. Authentication happens via cryptographic signature verification using paragonie/ecc
4. Subscription status gates access to forum features
5. Users table includes soft deletes (deleted_at column)

### Database Models
- **User**: Authenticated via public key, linked to subscriptions, supports soft deletes
- **Board**: Top-level forum categories with shifting stats for mining
- **Thread**: Discussion topics within boards with PoW requirements
- **Post**: Individual messages with support for image attachments and nested replies
- **ImageLibrary**: File management system for uploaded images
- **Subscription/Payment**: Stripe billing integration
- **FriendCode**: Temporary access codes for new users
- **ProofOfWork/Mining**: Custom PoW system with hover-based targeting

### Payment System
- Stripe integration with webhook handling via StripeWebhookController
- Subscription plans managed through admin interface
- Friend codes provide temporary access without payment
- Commands handle payment status checks and subscription renewals

### Recent Additions
- **Image Library System**: Complete file upload and management system
- **Simple Mining System**: Hover-based targeting interface for mining operations
- **API Controllers**: Enhanced API support for external/mobile access
- **Board Shifting Stats**: Dynamic statistics for mining difficulty adjustment
- **Global Stats Composer**: View composer for displaying site-wide statistics

### Development Environment
- Vite 6.0 for frontend asset bundling with Laravel plugin
- TailwindCSS 4.1 for styling
- TypeScript 5.0 with strict type checking
- Concurrently runs multiple services via `composer run dev`:
  - Laravel server (0.0.0.0:8000)
  - Queue worker
  - Log viewer (pail)
  - Vite dev server
- Uses SQLite by default for development
- Node.js >=18.0.0 and npm >=8.0.0 required