# Haichan

A modern, cryptographically-secured imageboard built with Laravel, TypeScript, and Rust clients, featuring a unique proof-of-work mining system and secp256k1 public key authentication.

## 🚀 Features

### Forum System
- **Hierarchical Structure**: Boards → Threads → Posts organization
- **Reply System**: Nested replies with threading support
- **Real-time Mining**: Interactive proof-of-work system integrated into browsing

### Proof-of-Work Mining
- **Browser-Based Mining**: JavaScript SHA-256 mining with adjustable intensity
- **Multiple Mining Modes**: IDLE (~100 H/s), ACTIVE (~1K H/s), HYPER (~3K+ H/s)
- **Vanity Hash Patterns**: Mining for hashes starting with specific patterns (`21e8`, `21e800`, etc.)
- **Rarity System**: Different proof rarities from COMMON to LEGENDARY
- **Real-time Dashboard**: Live mining statistics and visual feedback

### Multi-Platform Support
- **Web Application**: Laravel-based web interface
- **TUI Client**: Terminal-based client built in Rust with Ratatui
- **Rust Client Library**: Shared functionality for terminal applications

## 🏗️ Architecture

### Backend (Laravel 12)
- **PHP 8.2+** with modern Laravel features
- **SQLite** database (development) with MySQL/PostgreSQL support
- **Queue System** for background job processing
- **Stripe Webhook** handling for payment processing
- **API Routes** for client applications

### Frontend (TypeScript + Vite)
- **TypeScript** for type-safe client-side code
- **Vite** for fast development and production builds
- **Noble secp256k1** for cryptographic operations
- **Web Crypto API** for SHA-256 mining
- **Real-time Mining UI** with visual feedback

### Rust Clients
- **Tokio** async runtime
- **Reqwest** for HTTP client functionality
- **Ratatui** for terminal user interface
- **secp256k1** for cryptographic operations
- **Shared crate** for common functionality

## 🛠️ Development Setup

### Prerequisites
- **PHP 8.2+** with Composer
- **Node.js 18+** with npm
- **Rust** (for TUI client)
- **SQLite** (included with PHP)

### Quick Start

1. **Clone and setup web application**:
```bash
cd web-app
composer install
npm install
cp .env.example .env
php artisan key:generate
php artisan migrate
```

2. **Start development servers**:
```bash
# All-in-one development command (recommended)
composer run dev

# Or manually start individual services:
php artisan serve          # Laravel server
php artisan queue:listen   # Queue worker  
php artisan pail          # Log viewer
npm run dev               # Vite dev server
```

3. **Generate cryptographic keypairs**:
```bash
npm run generate-keypair 5  # Generate 5 keypairs
```

### Rust Clients

**TUI Client**:
```bash
cd tui-client
cargo run
```

**Rust Client Library**:
```bash
cd rust-client  
cargo build
```

## 🔧 Available Commands

### Laravel Backend
- `composer run dev` - Start all development servers
- `composer run test` - Run PHP tests
- `php artisan serve` - Laravel development server
- `php artisan test` - Run PHPUnit tests
- `php artisan queue:listen --tries=1` - Start queue worker
- `php artisan pail --timeout=0` - Watch Laravel logs
- `vendor/bin/pint` - Run Laravel Pint code formatter

### Frontend
- `npm run dev` - Start Vite development server
- `npm run build` - Build production assets
- `npm run type-check` - TypeScript type checking
- `npm run generate-keypair [count]` - Generate secp256k1 keypairs

### Rust
- `cargo run` - Run TUI client
- `cargo build --release` - Build optimized binary
- `cargo test` - Run Rust tests

## 📊 Mining System

### How It Works
1. **Browser Mining**: JavaScript performs SHA-256 hashing in the browser
2. **Target Patterns**: Mine for hashes starting with specific hex patterns
3. **Proof Submission**: Valid proofs are submitted to the Laravel backend
4. **Thread Ranking**: Mining contributes to thread and user rankings

### Mining Patterns & Difficulty
- `21` - IDLE difficulty (~1/256 chance)
- `21e8` - COMMON difficulty (~1/65,536 chance)
- `21e80` - COMMON+ difficulty (~1/1M chance)
- `21e800` - UNCOMMON difficulty (~1/16M chance)
- `21e8000` - RARE difficulty (~1/268M chance)
- `000021e8` - LEGENDARY difficulty (~1/4B chance)

### Mining Modes
- **IDLE**: Low CPU usage, mines `21e8` pattern
- **ACTIVE**: Normal intensity, full `21e8` pattern mining  
- **HYPER**: Maximum intensity, high hash rate mining

## 🔐 Authentication Flow

1. **Key Generation**: Users generate secp256k1 keypairs using the TypeScript utility
2. **Public Key Registration**: Public keys are stored in the `allowed_public_keys` database table
3. **Message Signing**: Users sign authentication messages with their private key
4. **Signature Verification**: Server verifies signatures against stored public keys
5. **Session Creation**: Valid signatures create authenticated sessions

## 💳 Payment & Subscription

### Subscription Plans
- Managed through Laravel admin interface
- Stripe integration for payment processing
- Webhook handling for subscription updates
- Automatic access control based on subscription status

### Friend Codes
- Temporary access codes for new users
- Generated by administrators
- Allow limited forum access without payment
- Automatically expire after specified duration

## 🗄️ Database Models

### Core Models
- **User**: Public key authentication, subscription linking
- **Board**: Top-level forum categories
- **Thread**: Discussion topics within boards  
- **Post**: Individual messages and replies
- **ProofOfWork**: Mining submission records
- **MiningSession**: User mining statistics

### Payment Models  
- **Subscription**: User subscription records
- **Payment**: Stripe payment tracking
- **SubscriptionPlan**: Available subscription tiers
- **FriendCode**: Temporary access codes

## 🚦 API Endpoints

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
- `POST /api/boards/{board}/threads` - Create new thread
- `POST /api/threads/{thread}/posts` - Create new post

## 📁 Project Structure

```
├── web-app/                 # Laravel application
│   ├── app/
│   │   ├── Http/Controllers/    # API and web controllers
│   │   ├── Models/             # Eloquent models
│   │   ├── Services/           # Business logic
│   │   └── Console/Commands/   # Artisan commands
│   ├── resources/
│   │   ├── views/              # Blade templates
│   │   └── js/                 # Frontend JavaScript/TypeScript
│   ├── routes/                 # Web, API, and console routes
│   ├── database/migrations/    # Database schema
│   └── src/crypto/             # TypeScript crypto utilities
├── tui-client/              # Terminal UI client (Rust)
├── rust-client/             # Rust client library
├── shared/                  # Shared utilities
└── README.md               # This file
```

## 🔧 Configuration

### Environment Variables
Key configuration options in `.env`:

```env
APP_URL=http://localhost:8000
DB_DATABASE=database/database.sqlite

# Stripe configuration
STRIPE_KEY=pk_test_...
STRIPE_SECRET=sk_test_...
STRIPE_WEBHOOK_SECRET=whsec_...

# Queue configuration  
QUEUE_CONNECTION=database
```

### Mining Configuration
Mining parameters can be adjusted in `resources/js/global-mining.js`:
- Hash rate targeting
- Difficulty patterns
- Visual feedback settings
- Mining intensity levels

## 🧪 Testing

### PHP Tests
```bash
composer run test           # Run all tests with config clearing
php artisan test           # Run PHPUnit tests directly
php artisan test --filter  # Run specific test methods
```

### TypeScript
```bash
npm run type-check         # TypeScript compiler check
```

### Rust Tests  
```bash
cargo test                 # Run Rust test suite
cargo test --release       # Run optimized tests
```

## 📈 Performance

### Mining Performance
- **Browser Mining**: 100-3000+ H/s depending on hardware and mode
- **Memory Usage**: Minimal, mining operations are stateless
- **CPU Usage**: Adjustable via mining intensity settings
- **Network**: Efficient proof submission with batching

### Web Application
- **Laravel Octane**: Available for enhanced performance
- **Database**: SQLite for development, PostgreSQL/MySQL for production
- **Caching**: Redis support for session and cache storage
- **Queue System**: Database queues with supervisor for reliability

## 🛡️ Security Considerations

### Cryptographic Security
- **secp256k1**: Industry-standard elliptic curve cryptography
- **Message Signing**: Prevents replay attacks with timestamps
- **Public Key Verification**: Server-side signature validation
- **No Private Key Storage**: Private keys never leave user devices

### Web Security
- **CSRF Protection**: Laravel CSRF middleware enabled
- **Rate Limiting**: API endpoints protected against abuse  
- **Input Validation**: Strict validation on all user inputs
- **SQL Injection**: Eloquent ORM prevents SQL injection
- **XSS Protection**: Blade templates escape output by default

### Mining Security
- **Hash Verification**: Server validates all submitted hashes
- **Pattern Validation**: Strict pattern matching prevents fake proofs
- **Rate Limiting**: Mining submission rate limits
- **Proof Expiration**: Time-based proof validity windows

## 🤝 Contributing

1. **Fork** the repository
2. **Create** a feature branch (`git checkout -b feature/amazing-feature`)
3. **Commit** changes (`git commit -m 'Add amazing feature'`)
4. **Push** to branch (`git push origin feature/amazing-feature`)
5. **Open** a Pull Request

### Code Style
- **PHP**: Laravel Pint for code formatting (`vendor/bin/pint`)
- **TypeScript**: Prettier for consistent formatting
- **Rust**: `cargo fmt` for standard Rust formatting

### Testing Requirements
- All new features must include tests
- PHP tests should maintain >80% code coverage
- TypeScript functions should include type tests
- Rust code should include unit tests

## 📄 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## 🏆 Acknowledgments

- **Laravel Community** for the excellent web framework
- **Rust Community** for the fast and safe systems programming language
- **Noble Curves** for the secp256k1 TypeScript implementation
- **Ratatui** for the terminal user interface framework
- **Stripe** for reliable payment processing

## 📞 Support

- **GitHub Issues**: Report bugs and request features
- **Documentation**: Check the source code and comments for development guidance
- **Community**: Join discussions in GitHub Discussions

---

**Built with ❤️ using Laravel, TypeScript, and Rust**
