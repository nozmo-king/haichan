# Forum TUI Client (Rust)

A high-performance Terminal User Interface client for the Laravel Forum API, built with Rust and ratatui.

## Features

- 🔐 Secure authentication with public key cryptography
- 📋 Browse forum boards and threads
- 📝 Create new threads and replies
- ⚡ Fast, responsive terminal interface
- 🎨 Clean, intuitive keyboard-driven navigation

## Installation

1. Ensure you have Rust installed (https://rustup.rs/)
2. Build the project:

```bash
cd rust-client
cargo build --release
```

## Usage

```bash
cargo run
# or after building:
./target/release/forum-tui-rust
```

## Controls

### Global
- `q` - Quit application
- `h` - Show help
- `Esc` - Go back/cancel

### Login
- `Tab` - Switch between input fields
- `Enter` - Submit (get challenge or login)

### Navigation
- `↑↓` - Navigate lists
- `Enter` - Select item
- `b` - Go to boards

### Boards
- `Enter` - View board threads
- `r` - Refresh boards

### Threads
- `Enter` - View thread detail
- `n` - Create new thread
- `t` - Refresh threads
- `b` - Back to boards

### Thread Detail
- `r` - Create reply
- `t` - Back to threads
- `b` - Back to boards

### Creating Content
- `Tab` - Switch input fields (when multiple)
- `Ctrl+S` - Submit
- `Esc` - Cancel

## API Configuration

The client connects to the Laravel API at `http://localhost:8000/api` by default.

## Authentication Flow

1. Enter your public key
2. Press Enter to get a challenge from the server
3. Sign the challenge with your private key
4. Enter the signature
5. Press Enter to login

## Dependencies

- `ratatui` - Terminal UI framework
- `crossterm` - Cross-platform terminal manipulation
- `tokio` - Async runtime
- `reqwest` - HTTP client
- `serde` - Serialization framework
- `anyhow` - Error handling

## Architecture

- `api.rs` - HTTP client and API data structures
- `app.rs` - Application state and business logic
- `ui.rs` - Terminal user interface rendering
- `event.rs` - Keyboard event handling
- `main.rs` - Application entry point and main loop

## Performance

Built with Rust for maximum performance and memory safety. The async architecture ensures responsive UI even during network operations.