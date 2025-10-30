# AI Assistant Instructions for Haichan Web App

## 1. Codebase Overview

This document provides instructions for AI assistants (like Claude and Copilot) on how to work with the Haichan web application codebase. The project is a Laravel-based forum with a custom cryptographic authentication system and a Proof-of-Work (PoW) mechanism.

A recent audit and refactoring effort has been completed to address several critical security vulnerabilities and to improve the overall architecture of the PoW system. Please adhere to the new architectural patterns and best practices outlined in this document.

## 2. Key Architectural Changes

### 2.1. `PointCalculationService`

A new service, `App\Services\PointCalculationService`, has been created to consolidate all PoW point calculation logic. Previously, point calculation was scattered across multiple controllers, leading to inconsistencies and vulnerabilities.

**How to use it:**

1.  Inject the service in the controller's constructor:

    ```php
    use App\Services\PointCalculationService;

    protected $pointCalculationService;

    public function __construct(PointCalculationService $pointCalculationService)
    {
        $this->pointCalculationService = $pointCalculationService;
    }
    ```

2.  Call the `calculatePoints` method:

    ```php
    $points = $this->pointCalculationService->calculatePoints($pattern, $hash);
    ```

**DO NOT** implement custom point calculation logic in controllers. All point calculations MUST go through the `PointCalculationService`.

### 2.2. Consolidation of PoW Logic

The PoW submission logic has been consolidated into the following controllers:

*   `app/Http/Controllers/MiningController.php`: Handles the main mining dashboard and PoW submissions.
*   `app/Http/Controllers/ProofOfWorkController.php`: Handles PoW submissions from the forum (e.g., creating threads and replies). Contains some legacy methods that are being deprecated.
*   `app/Http/Controllers/SelfMiningController.php`: Handles the personal 21e8 achievement system.

When adding new PoW-related features, please use one of these controllers or create a new one that follows the same architectural pattern.

## 3. How to Submit PoW

There are three main PoW submission endpoints:

1.  **General Mining (`/api/mining/submit-proof`):** This endpoint is used for general-purpose mining from the mining dashboard. It is handled by `MiningController@submitMiningProof`.

2.  **Forum PoW (`/api/pow/thread/commit`, `/api/pow/reply/commit`):** These endpoints are used for submitting PoW when creating threads and replies. They are handled by `PowController`.

3.  **Self-Mining (`/api/self-mining/submit`):** This endpoint is used for the personal 21e8 achievement system. It is handled by `SelfMiningController@submitPersonal21e8`.

All PoW submission endpoints now have rate limiting and proper validation. Please ensure that any new PoW endpoints also have these security features.

## 4. How to Add New Features

When adding new features, please follow these guidelines:

*   **Use the existing services:** Use the `ChallengeVerifier` for PoW verification and the `PointCalculationService` for point calculation.
*   **Follow the controller structure:** Create new methods in the existing controllers or create new controllers that follow the same pattern.
*   **Add tests:** Create new tests for any new features to ensure they are working correctly and do not introduce new vulnerabilities.
*   **Update documentation:** If you make any changes to the architecture or add new features, please update this document accordingly.

## 5. Frontend Development

Frontend developers should be aware of the following:

*   **API endpoints:** The API endpoints for PoW submission are documented in `routes/api.php` and `routes/web.php`.
*   **Error handling:** The API will return a 429 status code if the rate limit is exceeded. The frontend should handle this gracefully.
*   **CSRF protection:** All POST requests to the web routes are protected by CSRF. Ensure that the CSRF token is included in all relevant requests.

## 6. Security Best Practices

The following security vulnerabilities have been addressed:

*   **Dummy Hash Bypass:** The system now checks for suspicious hash patterns (e.g., too many zeros) instead of just a single dummy hash.
*   **Race Conditions:** The `proof_of_works` table has a unique constraint on the `hash` column to prevent duplicate submissions. The application code also handles the duplicate key exception gracefully.
*   **Nonce Verification:** The system now correctly verifies the nonce for all PoW submissions.
*   **Inconsistent Point Calculation:** All point calculation is now handled by the `PointCalculationService`.
*   **Missing Rate Limiting:** All PoW submission endpoints now have rate limiting.

When writing new code, please be mindful of these vulnerabilities and follow these best practices:

*   **Always validate user input:** Use Laravel's validation features to validate all user input.
*   **Use prepared statements:** Use Laravel's Eloquent ORM or Query Builder to prevent SQL injection.
*   **Escape output:** Use Blade's `{{ }}` syntax to escape all output and prevent XSS.
*   **Use CSRF protection:** Ensure that all state-changing requests are protected by CSRF.
*   **Add rate limiting:** Add rate limiting to all sensitive endpoints.
