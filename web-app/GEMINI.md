# GEMINI.md

## Project Overview

This project is a Laravel-based web application named "haichan". It appears to be a forum or community platform with a strong emphasis on custom cryptographic authentication and a Proof-of-Work (PoW) system.

**Main Technologies:**

*   **Backend:** Laravel (PHP)
*   **Frontend:** Vite, Tailwind CSS, JavaScript (likely with Vue.js, but not explicitly confirmed)
*   **Database:** SQLite (default), MySQL/MariaDB
*   **Authentication:** Custom cryptographic authentication using public/private key pairs (secp256k1), with a backup login method and anonymous mode.

**Key Features:**

*   **Forum:** A forum with boards, threads, and posts.
*   **Proof-of-Work (PoW):** A PoW system is used for various actions, including mining. The `CLAUDE.md` file mentions "MOUSEOVER IS HOW MINING IS DICTATED" and "rolling 60-second window of mouse movement as proof of work".
*   **Chat:** A real-time chat system.
*   **Image Library:** A library for storing and managing images.
*   **Point Shop:** A shop where users can spend points.
*   **Admin Panel:** An admin panel for managing users, the forum, and other aspects of the application.

## Building and Running

**Development:**

The primary command to start the development environment is:

```bash
composer dev
```

This command will:

1.  Start the PHP development server.
2.  Start the queue listener.
3.  Start the log tailer (`pail`).
4.  Start the Vite development server.

**Testing:**

To run the test suite, use the following command:

```bash
composer test
```

**Frontend:**

*   To start the Vite development server: `npm run dev`
*   To build the frontend assets for production: `npm run build`

## Development Conventions

*   **Authentication:** The application uses a custom cryptographic authentication system. The `app/Models/User.php` file contains the logic for signature verification.
*   **Frontend:** The main JavaScript entry points are `resources/js/app.js` and `resources/js/haichan-unified.js`. The frontend uses Tailwind CSS for styling.
*   **Routes:** The application's routes are defined in `routes/web.php`.
*   **Database:** The default database is SQLite. The database schema is managed through Laravel migrations in the `database/migrations` directory.
