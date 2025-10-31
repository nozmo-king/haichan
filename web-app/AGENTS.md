# Repository Guidelines

This guide explains how to work on this repository (Laravel + Vite/Tailwind frontend, Rust PoW submodules). Keep changes small, documented, and tested.

## Project Structure & Module Organization
- Laravel app: `app/`, routes in `routes/`, views/assets in `resources/`, public assets in `public/`.
- Frontend tooling: Vite config `vite.config.js`, Node code in `src/`, dependencies in `package.json`.
- Proof-of-Work modules: Rust/WASM in `pow/` (see `README.md`).
- Configuration: `.env`, examples in `.env.example`.
- Tests: PHP in `tests/` with suites `Unit` and `Feature`; Rust tests under `pow/*`.
- Scripts and ops: `scripts/`, `setup-pow.sh`, deployment scripts in root.

## Build, Test, and Development Commands
- PHP deps: `composer install` — install Laravel dependencies.
- Env + key: `cp .env.example .env` then `php artisan key:generate`.
- Migrate DB: `php artisan migrate`.
- Serve API: `php artisan serve` (defaults to http://127.0.0.1:8000).
- Frontend dev: `npm run dev` — Vite dev server; build with `npm run build`.
- TS checks: `npm run type-check`.
- PHPUnit: `php artisan test` (config in `phpunit.xml`).
- Rust PoW: `cd pow/verifier && cargo test`; WASM build in `pow/miner-wasm` via `wasm-pack build`.

## Coding Style & Naming Conventions
- PHP: PSR-12 style; 4-space indent; classes `StudlyCase`, methods `camelCase`, constants `UPPER_SNAKE_CASE`.
- TypeScript: 2-space indent; variables/functions `camelCase`, types/interfaces `PascalCase`.
- Blade/Views: keep logic minimal; prefer ViewModels/Resources in PHP.
- Lint/format: use IDE formatting consistent with PSR-12 and TypeScript defaults; run `npm run type-check` before PRs.

## Testing Guidelines
- Frameworks: PHPUnit for PHP; Rust `cargo test`; WASM with `wasm-pack test`.
- Test layout: place PHP tests in `tests/Unit` or `tests/Feature`; mirror namespaces and filename patterns like `ThingServiceTest.php`.
- Coverage: include new code paths and error cases; keep fast, isolated tests (use sqlite memory in tests via `phpunit.xml`).
- Run: `php artisan test` locally and in CI before opening PRs.

## Commit & Pull Request Guidelines
- Commits: concise, imperative subject (≤72 chars), body explains why. Example: `Fix: prevent null author on thread.commit`.
- Scope PRs narrowly; link issues (e.g., `Closes #123`).
- PR checklist: description of change, screenshots for UI, reproduction/validation steps, notes on migration/rollback.
- Keep `README.md` or docs updated when behavior or commands change.

## Security & Configuration Tips
- Never commit secrets; use `.env` and keep `.env.example` updated.
- Validate inputs in controllers/requests; avoid leaking stack traces in production.
- Run `php artisan config:cache` and `route:cache` for production; ensure HTTPS behind proxies per server setup.

## Agent-Specific Instructions
- Respect this AGENTS.md across the repo.
- Avoid large refactors without an issue and plan.
- Prefer adding focused tests with each fix.

## CI & Linting
- GitHub Actions runs type-checks, builds, linters, and tests for Node, PHP, and Rust on PRs.
- Node: `npm run type-check`, `npm run build`, `npm run lint`.
- PHP: `php artisan test`, `./vendor/bin/pint --test`.
- Rust: `cargo test` in `pow/verifier` (WASM tests optional if browser present).
