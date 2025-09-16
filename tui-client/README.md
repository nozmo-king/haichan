# Forum TUI Client

A Terminal User Interface client for the Laravel Forum API.

## Features

- Login with public key authentication
- Browse boards and threads
- Create new threads and replies
- Clean terminal interface with keyboard shortcuts

## Installation

```bash
cd tui-client
npm install
```

## Usage

```bash
npm start
```

## Keyboard Shortcuts

- `l` - Login with public key
- `b` - Show boards (after login)
- `t` - Show threads in current board
- `n` - Create new thread (in a board)
- `r` - Create reply (in a thread)
- `h` - Show help
- `q` - Quit

## Setup

1. Make sure your Laravel API is running on `http://localhost:8000`
2. Have your public key ready for authentication
3. Start the TUI client with `npm start`

## API Endpoints Used

- `POST /api/auth/challenge` - Get authentication challenge
- `POST /api/auth/login` - Login with signature
- `GET /api/boards` - List all boards
- `GET /api/boards/{code}/threads` - List threads in a board
- `POST /api/boards/{code}/threads` - Create new thread
- `POST /api/boards/{code}/threads/{id}/replies` - Create reply